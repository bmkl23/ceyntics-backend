<?php

namespace App\Services;

use App\Models\BorrowRecord;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class BorrowService
{
    public function getAllBorrows(array $filters = []): object
    {
        $query = BorrowRecord::with(['item', 'creator'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        // Overdue filter
        if (!empty($filters['overdue']) && $filters['overdue']) {
            $query->where('status', 'active')
                  ->where('expected_return', '<', now());
        }

        return $query->get();
    }

    public function createBorrow(array $data, int $userId): BorrowRecord
    {
        // DB::transaction ensures ALL or NOTHING
        // lockForUpdate() prevents race conditions
        return DB::transaction(function () use ($data, $userId) {

            // Lock this item row so no other request can read/write it
            // until this transaction completes
            $item = InventoryItem::lockForUpdate()
                ->findOrFail($data['item_id']);

            // Business Rule: Cannot borrow more than available
            if ($item->quantity < $data['quantity_borrowed']) {
                throw new \Exception(
                    "Insufficient stock. Available: {$item->quantity}, Requested: {$data['quantity_borrowed']}",
                    400
                );
            }

            // Business Rule: Cannot borrow damaged or missing items
            if (in_array($item->status, ['damaged', 'missing'])) {
                throw new \Exception(
                    "Cannot borrow item with status: {$item->status}",
                    400
                );
            }

            $oldQty    = $item->quantity;
            $oldStatus = $item->status;

            // Deduct stock
            $item->quantity -= $data['quantity_borrowed'];

            // Update status to borrowed if all stock is out
            if ($item->quantity === 0) {
                $item->status = 'borrowed';
            }

            $item->save();

            // Create borrow record
            $borrow = BorrowRecord::create([
                'item_id'           => $item->id,
                'borrower_name'     => $data['borrower_name'],
                'contact'           => $data['contact'],
                'quantity_borrowed' => $data['quantity_borrowed'],
                'quantity_returned' => 0,
                'borrow_date'       => $data['borrow_date'],
                'expected_return'   => $data['expected_return'],
                'status'            => 'active',
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // Log the borrow action
            AuditLogService::log(
                'item.borrowed', 'InventoryItem', $item->id,
                ['quantity' => $oldQty,    'status' => $oldStatus],
                ['quantity' => $item->quantity, 'status' => $item->status],
                "Item borrowed by {$data['borrower_name']}, qty: {$data['quantity_borrowed']}"
            );

            return $borrow->load('item');
        });
    }

    public function processReturn(BorrowRecord $borrow, int $returnQty): BorrowRecord
    {
        return DB::transaction(function () use ($borrow, $returnQty) {

            // Lock both records
            $borrow = BorrowRecord::lockForUpdate()->findOrFail($borrow->id);
            $item   = InventoryItem::lockForUpdate()->findOrFail($borrow->item_id);

            // Validate return quantity
            $remainingBorrowed = $borrow->quantity_borrowed - $borrow->quantity_returned;

            if ($returnQty > $remainingBorrowed) {
                throw new \Exception(
                    "Return quantity ({$returnQty}) exceeds remaining borrowed ({$remainingBorrowed})",
                    400
                );
            }

            if ($returnQty <= 0) {
                throw new \Exception('Return quantity must be greater than 0', 400);
            }

            $oldItemQty     = $item->quantity;
            $oldBorrowStatus = $borrow->status;

            // Add stock back
            $item->quantity += $returnQty;

            // Update item status back to in_store
            if ($item->status === 'borrowed') {
                $item->status = 'in_store';
            }
            $item->save();

            // Update borrow record
            $borrow->quantity_returned += $returnQty;

            // Determine new borrow status
            if ($borrow->quantity_returned >= $borrow->quantity_borrowed) {
                $borrow->status        = 'returned';
                $borrow->actual_return = now()->toDateString();
            } else {
                $borrow->status = 'partially_returned';
            }

            $borrow->save();

            // Log the return
            AuditLogService::log(
                'item.returned', 'InventoryItem', $item->id,
                ['quantity' => $oldItemQty,     'borrow_status' => $oldBorrowStatus],
                ['quantity' => $item->quantity,  'borrow_status' => $borrow->status],
                "Returned {$returnQty} of {$borrow->quantity_borrowed} by {$borrow->borrower_name}"
            );

            return $borrow->fresh()->load('item');
        });
    }
}
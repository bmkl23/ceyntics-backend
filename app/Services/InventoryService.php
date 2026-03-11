<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Storage;

class InventoryService
{
    public function getAllItems(array $filters = []): object
    {
        $query = InventoryItem::with(['place.cupboard', 'creator'])
            ->whereNull('deleted_at');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by place
        if (!empty($filters['place_id'])) {
            $query->where('place_id', $filters['place_id']);
        }

        // Filter by category
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        // Search by name or code
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ilike', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'ilike', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

  public function createItem(array $data, int $userId): InventoryItem
{
    // Handle image upload
    $imagePath = null;
    if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
        $imagePath = $data['image']->store('items', 'public');
    }

    $item = InventoryItem::create([
        'name'          => $data['name'],
        'code'          => $data['code'],
        'quantity'      => $data['quantity'],
        'serial_number' => $data['serial_number'] ?? null,
        'image_path'    => $imagePath,
        'description'   => $data['description'] ?? null,
        'category'      => $data['category'] ?? null,
        'place_id'      => $data['place_id'],
        'status'        => 'in_store',
        'min_quantity'  => $data['min_quantity'] ?? 1,
        'created_by'    => $userId,
    ]);

    AuditLogService::log(
        'item.created', 'InventoryItem', $item->id,
        [], $item->toArray(),
        "Item {$item->name} ({$item->code}) created with qty {$item->quantity}"
    );

    return $item->load('place.cupboard');
}
    public function updateItem(InventoryItem $item, array $data): InventoryItem
    {
        $oldValues = $item->only([
            'name', 'code', 'description',
            'category', 'place_id', 'serial_number', 'min_quantity'
        ]);

        // Handle new image
        if (!empty($data['image'])) {
            // Delete old image
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $data['image']->store('items', 'public');
        }

        $item->update(array_filter([
            'name'          => $data['name']          ?? null,
            'code'          => $data['code']          ?? null,
            'description'   => $data['description']   ?? null,
            'category'      => $data['category']      ?? null,
            'place_id'      => $data['place_id']      ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'min_quantity'  => $data['min_quantity']  ?? null,
            'image_path'    => $data['image_path']    ?? null,
        ], fn($v) => !is_null($v)));

        AuditLogService::log(
            'item.updated', 'InventoryItem', $item->id,
            $oldValues,
            $item->only(['name', 'code', 'description', 'category', 'place_id']),
            "Item {$item->name} updated"
        );

        return $item->fresh()->load('place.cupboard');
    }

    public function updateQuantity(InventoryItem $item, string $action, int $amount): InventoryItem
    {
        $oldQty = $item->quantity;

        if ($action === 'increment') {
            $item->quantity += $amount;
        } elseif ($action === 'decrement') {
            // Cannot go below zero
            if ($item->quantity < $amount) {
                throw new \Exception('Insufficient quantity. Available: ' . $item->quantity, 400);
            }
            $item->quantity -= $amount;
        } else {
            throw new \Exception('Invalid action. Use increment or decrement', 400);
        }

        $item->save();

        AuditLogService::log(
            'quantity.changed', 'InventoryItem', $item->id,
            ['quantity' => $oldQty],
            ['quantity' => $item->quantity],
            "Quantity {$action}ed by {$amount}. {$oldQty} → {$item->quantity}"
        );

        return $item->fresh();
    }

    public function updateStatus(InventoryItem $item, string $newStatus): InventoryItem
    {
        $oldStatus = $item->status;

        if ($oldStatus === $newStatus) {
            throw new \Exception('Item already has this status', 400);
        }

        $item->update(['status' => $newStatus]);

        AuditLogService::log(
            'status.changed', 'InventoryItem', $item->id,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            "Status changed: {$oldStatus} → {$newStatus}"
        );

        return $item->fresh();
    }

    public function deleteItem(InventoryItem $item): void
    {
        AuditLogService::log(
            'item.deleted', 'InventoryItem', $item->id,
            $item->toArray(), [],
            "Item {$item->name} ({$item->code}) soft deleted"
        );

        $item->delete(); // soft delete
    }
}
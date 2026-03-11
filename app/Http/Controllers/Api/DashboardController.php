<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BorrowRecord;
use App\Models\InventoryItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function stats()
{
    return response()->json([
        'total_items'     => InventoryItem::count(),

        // Count active borrow RECORDS, not item status
        'borrowed_items'  => BorrowRecord::whereIn('status', ['active', 'partially_returned'])->count(),

        'damaged_items'   => InventoryItem::where('status', 'damaged')->count(),
        'missing_items'   => InventoryItem::where('status', 'missing')->count(),
        'low_stock_items' => InventoryItem::whereRaw('quantity <= min_quantity')->count(),
        'overdue_borrows' => BorrowRecord::where('status', 'active')
            ->where('expected_return', '<', now()->toDateString())
            ->count(),
        'total_users'     => User::where('is_active', true)->count(),
        'recent_activity' => ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(),
    ]);
}

}
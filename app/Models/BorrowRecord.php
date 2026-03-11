<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BorrowRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'borrower_name',
        'contact',
        'quantity_borrowed',
        'quantity_returned',
        'borrow_date',
        'expected_return',
        'actual_return',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'borrow_date'       => 'date',
        'expected_return'   => 'date',
        'actual_return'     => 'date',
        'quantity_borrowed' => 'integer',
        'quantity_returned' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->expected_return->isPast();
    }
}
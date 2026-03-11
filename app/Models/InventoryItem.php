<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'quantity',
        'serial_number',
        'image_path',
        'description',
        'category',
        'place_id',
        'status',
        'min_quantity',
        'created_by',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'min_quantity' => 'integer',
    ];

    // Append image_url to every response
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}
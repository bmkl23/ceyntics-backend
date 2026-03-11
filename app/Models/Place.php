<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'cupboard_id',
        'name',
        'description',
        'created_by',
    ];

    public function cupboard()
    {
        return $this->belongsTo(Cupboard::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
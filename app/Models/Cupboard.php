<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cupboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'description',
        'created_by',
    ];

    public function places()
    {
        return $this->hasMany(Place::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('quantity')->default(0);
            $table->string('serial_number')->nullable();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('place_id')->constrained('places');
            $table->enum('status', ['in_store', 'borrowed', 'damaged', 'missing'])->default('in_store');
            $table->integer('min_quantity')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
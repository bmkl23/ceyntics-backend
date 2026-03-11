<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->string('borrower_name');
            $table->string('contact');
            $table->integer('quantity_borrowed');
            $table->integer('quantity_returned')->default(0);
            $table->date('borrow_date');
            $table->date('expected_return');
            $table->date('actual_return')->nullable();
            $table->enum('status', ['active', 'partially_returned', 'returned', 'overdue'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_records');
    }
};
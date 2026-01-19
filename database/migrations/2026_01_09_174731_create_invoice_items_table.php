<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('item_id');
            $table->string('item_name');
            $table->decimal('per_item_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            
            $table->decimal('returned_amount', 15, 2)->default(0);
            $table->enum('return_status', ['none', 'partial', 'full'])->default('none');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionReturnsTable extends Migration
{
    public function up()
    {
        Schema::create('transaction_returns', function (Blueprint $table) {

            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->string('return_no')->unique();

            // full or partial return
            $table->enum('return_type', ['full', 'partial']);

            // cash / bank / wallet / adjustment
            $table->enum('return_amount_in', [
                'cash',
                'easypaisa',
                'jazzcash',
                'bank_transfer',
                'credit_card',
                'other'
            ])->nullable();

            // amount returned in this transaction
            $table->decimal('return_amount', 15, 2);

            $table->text('reason')->nullable();

            // user who processed return
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // indexes
            $table->index('invoice_id');

            // soft delete for audit safety
            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_returns');
    }
}
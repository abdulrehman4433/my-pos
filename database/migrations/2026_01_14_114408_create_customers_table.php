<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('customer_code', 30)->unique();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable()->index();
            $table->string('address', 150)->nullable();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }

};

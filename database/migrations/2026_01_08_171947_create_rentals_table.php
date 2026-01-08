<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id('rental_id'); // Note: Fixed spelling from 'rentel' to 'rental'
            $table->string('rental_code')->unique(); // Fixed spelling
            $table->string('rental_product');
            $table->string('rental_person');
            $table->string('rental_person_phone');
            $table->text('rental_person_address');
            $table->decimal('rental_price', 10, 2);
            $table->string('rental_duration'); // Fixed spelling
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('rental_code');
            $table->index('rental_person');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};

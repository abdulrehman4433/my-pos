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
            $table->string('rental_code')->unique(); 
            $table->string('rental_product');
            $table->string('rental_person');
            $table->string('rental_person_phone');
            $table->text('rental_person_address');
            $table->decimal('rental_price', 10, 2);
            $table->string('rental_duration'); 
            $table->date('rental_start_date'); 
            $table->date('rental_end_date');
            $table->enum('rental_status', ['ongoing', 'completed', 'overdue'])->default('ongoing'); 
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id('maintenance_id'); 
            $table->string('maintenance_code')->unique(); 
            $table->string('maintenance_name');
            $table->text('maintenance_address');
            $table->string('maintenance_phone');
            $table->decimal('maintenance_price', 10, 2);
            $table->string('maintenance_duration'); 
            $table->text('maintenance_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('maintenance_code');
            $table->index('maintenance_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenances');
    }
};

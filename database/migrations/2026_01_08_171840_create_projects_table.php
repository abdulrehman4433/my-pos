<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->text('project_address');
            $table->string('project_phone');
            $table->decimal('project_price', 10, 2);
            $table->string('project_duration');
            $table->text('project_details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('project_code');
            $table->index('project_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};

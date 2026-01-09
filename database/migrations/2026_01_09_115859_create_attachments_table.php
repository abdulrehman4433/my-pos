<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relation: module_type = table name, module_id = record id
            $table->string('module_type'); // e.g., 'product', 'employee', 'invoice'
            $table->unsignedBigInteger('module_id'); // id of the module record
            
            $table->string('file_name'); // original file name
            $table->string('file_path'); // storage path
            $table->string('file_type')->nullable(); // mime type
            $table->string('file_extension', 10)->nullable(); // jpg, pdf, etc.
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

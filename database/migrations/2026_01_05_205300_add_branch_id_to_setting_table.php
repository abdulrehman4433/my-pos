<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchIdToSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->foreignId('branch_id')
                  ->after('setting_id')
                  ->constrained('branches')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
}

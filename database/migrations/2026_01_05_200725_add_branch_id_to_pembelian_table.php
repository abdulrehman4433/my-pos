
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchIdToPembelianTable extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            //
        });
    }
}

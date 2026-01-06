
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahDiskonToSettingTable extends Migration
{
    public function up()
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->smallInteger('discount')
                  ->default(0)
                  ->after('receipt_type');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUniqueConstraintOnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop old unique on product_name
            $table->dropUnique('products_product_name_unique');

            // Add new composite unique index
            $table->unique(
                ['product_name', 'brand', 'variant', 'unit', 'branch_id'],
                'products_unique_combination'
            );
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_unique_combination');
            $table->unique('product_name');
        });
    }
}

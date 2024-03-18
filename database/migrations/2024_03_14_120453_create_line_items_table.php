<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->bigInteger("id")->primary();
            $table->String("name");
            $table->String("product_id");
            $table->String("variation_id");
            $table->String("quantity");
            $table->String("tax_class");
            $table->String("subtotal");
            $table->String("subtotal_tax");
            $table->String("total");
            $table->String("total_tax");
            $table->json("taxes");
            $table->json("meta_data");
            $table->String("sku");
            $table->String("price");
            $table->String("image");
            $table->String("parent_name");
            $table->unsignedBigInteger('order_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_items');
    }
};

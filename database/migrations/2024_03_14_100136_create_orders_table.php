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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger("id")->primary();
            $table->string('number');
            $table->string('order_key');
            $table->string('status');
            $table->string('date_created');
            $table->string('total');
            $table->bigInteger('customer_id');
            $table->json('billing');
            $table->json('shipping');
            $table->string('customer_note');
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
        Schema::dropIfExists('orders');
    }
};

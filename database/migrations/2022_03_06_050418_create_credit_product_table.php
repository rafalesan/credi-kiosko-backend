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
        Schema::create('credit_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 100);
            $table->decimal('price', 17, 4);
            $table->decimal('quantity', 17, 4);
            $table->decimal('total', 17, 4);
            $table->timestamps();
            $table->foreign('credit_id')->references('id')->on('credits');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_product');
    }
};

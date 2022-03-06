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
        Schema::create('arrears', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cut_id');
            $table->timestamp('date');
            $table->decimal('balance_before_arrear', 17, 4);
            $table->decimal('balance_after_arrear', 17, 4);
            $table->decimal('percentage', 17, 4);
            $table->decimal('amount', 17, 4);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cut_id')->references('id')->on('cuts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arrears');
    }
};

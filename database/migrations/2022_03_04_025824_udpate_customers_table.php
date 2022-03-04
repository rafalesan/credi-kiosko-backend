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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->change();
            $table->string('nickname', 30)->nullable()->change();
            $table->string('email', 150)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 100)->nullable(false)->change();
            $table->string('nickname', 30)->nullable(false)->change();
            $table->string('email', 150)->nullable(false)->change();
        });
    }
};

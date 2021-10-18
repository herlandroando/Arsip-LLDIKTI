<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Jabatan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jabatan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_ijin');
            $table->string('nama', 120);
            $table->foreign('id_ijin')->references('id')->on('ijin')->restrictOnDelete();
        });
        Schema::table("pengguna", function (Blueprint $table) {
            $table->foreign('id_jabatan')->references('id')->on('jabatan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jabatan');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Ijin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ijin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
            $table->boolean('r_surat');
            $table->boolean('r_all_disposisi');
            $table->boolean('r_laporan');
            $table->boolean('d_surat');
            $table->boolean('d_miliksurat');
            $table->boolean('dp_surat');
            $table->boolean('w_disposisi');
            $table->boolean('w_suratmasuk');
            $table->boolean('w_suratkeluar');
            $table->boolean('admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ijin');
    }
}

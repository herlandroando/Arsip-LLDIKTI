<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Suratkeluar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suratkeluar', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('id_sifat');
            $table->unsignedBigInteger('id_pembuat')->nullable();
            $table->date('tanggal_surat');
            $table->string('no_surat', 150)->unique();
            $table->unsignedInteger('asal_surat')->nullable();
            $table->string('nama_pembuat',255);
            $table->string('tujuan', 255);
            $table->string('perihal', 255);
            // $table->string('tembusan', 500);
            $table->string('alamat', 255)->nullable();
            $table->string('isi_ringkas', 500)->nullable();
            $table->softDeletes();
            $table->dateTime("local_created_at")->nullable();
            $table->timestamps();

            $table->foreign('asal_surat')->references('id')->on('jabatan')->nullOnDelete();
            $table->foreign('id_sifat')->references('id')->on('sifatsurat')->restrictOnDelete();
            $table->foreign('id_pembuat')->references('id')->on('pengguna')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suratkeluar');
    }
}

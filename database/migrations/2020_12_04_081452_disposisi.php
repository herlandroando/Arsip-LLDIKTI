<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Disposisi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_suratmasuk')->default(0);
            $table->string('no_disposisi', 100)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('isi', 500)->nullable();
            $table->unsignedBigInteger('id_pengirim')->nullable();
            $table->unsignedInteger('id_jabatan')->nullable();
            $table->unsignedBigInteger('id_suratmasuk')->nullable();
            $table->dateTime("local_created_at")->nullable();
            $table->timestamp('expired_at');
            $table->timestamps();
            $table->foreign('id_jabatan')->references('id')->on('jabatan')->nullOnDelete();
            $table->foreign('id_pengirim')->references('id')->on('pengguna')->nullOnDelete();
            $table->foreign('id_suratmasuk')->references('id')->on('suratmasuk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disposisi');
    }
}

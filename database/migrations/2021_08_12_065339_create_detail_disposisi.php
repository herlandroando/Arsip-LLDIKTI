<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailDisposisi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_disposisi', function (Blueprint $table) {
            $table->id();
            $table->boolean("is_update_status");
            $table->string("keterangan",500);
            $table->unsignedBigInteger("id_disposisi");
            $table->unsignedBigInteger("id_pembuat")->nullable();
            $table->timestamp("created_at");
            $table->foreign('id_disposisi')->references('id')->on('disposisi')->cascadeOnDelete();
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
        Schema::dropIfExists('detail_disposisi');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FilesManagedTable extends Migration
{
    public function up()
    {
        Schema::create('files_managed', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_base_name');
            $table->string('file_name');
            $table->string('file_mime');
            $table->integer('file_size');
            $table->string('title')
                ->nullable();
            $table->string('alt')
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('files_managed');
    }
}

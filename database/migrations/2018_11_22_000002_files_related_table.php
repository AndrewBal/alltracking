<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FilesRelatedTable extends Migration
{
    public function up()
    {
        Schema::create('files_related', function (Blueprint $table) {
            $table->morphs('model');
            $table->string('type')
                ->default('medias');
            $table->integer('file_id')
                ->unsigned()
                ->nullable();
            $table->foreign('file_id')
                ->references('id')
                ->on('files_managed')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (Schema::hasTable('files_related')) {
            Schema::table('files_related', function (Blueprint $table) {
                $table->dropForeign('files_related_file_id_foreign');
                $table->drop();
            });
        }
    }
}

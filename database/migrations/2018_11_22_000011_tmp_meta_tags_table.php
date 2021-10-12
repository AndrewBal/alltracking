<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TmpMetaTagsTable extends Migration
{
    public function up()
    {
        Schema::create('tmp_meta_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->string('type')
                ->nullable();
            $table->text('meta_title')
                ->nullable();
            $table->text('meta_keywords')
                ->nullable();
            $table->text('meta_description')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tmp_meta_tags');
    }
}

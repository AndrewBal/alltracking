<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SearchIndexTable extends Migration
{
    public function up()
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->char('locale', 4)
                ->nullable();
            $table->string('title', 2047)
                ->nullable();
            $table->text('body')
                ->nullable();
            $table->boolean('status')
                ->default(1)
                ->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_index');
    }
}

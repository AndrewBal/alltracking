<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeDataFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('node_data_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('node_id')
                ->nullable()
                ->unsigned();
            $table->string('field');
            $table->text('data')
                ->nullable();
            $table->foreign('node_id')
                ->references('id')
                ->on('nodes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('node_data_fields');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NodeRelationFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('node_relation_fields', function (Blueprint $table) {
            $table->integer('id')
                ->unsigned();
            $table->integer('node_id')
                ->unsigned();
            $table->string('field');
            $table->tinyInteger('sort')
                ->default(0);
            $table->foreign('id')
                ->references('id')
                ->on('nodes')
                ->onDelete('cascade');
            $table->foreign('node_id')
                ->references('id')
                ->on('nodes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('node_relation_fields');
    }
}

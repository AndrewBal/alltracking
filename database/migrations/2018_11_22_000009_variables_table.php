<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VariablesTable extends Migration
{
    public function up()
    {
        Schema::create('variables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')
                ->unique();
            $table->string('name');
            $table->longText('value');
            $table->boolean('use_php')
                ->default(0);
            $table->text('comment')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('variables');
    }
}

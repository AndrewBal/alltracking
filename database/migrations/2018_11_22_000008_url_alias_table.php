<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UrlAliasTable extends Migration
{
    public function up()
    {
        Schema::create('url_alias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('alias');
            $table->char('locale', 2)
                ->nullable();
            $table->morphs('model');
            $table->string('model_title', 511)
                ->nullable();
            $table->boolean('sitemap')
                ->default(1);
            $table->enum('robots', [
                'index, follow',
                'noindex, follow',
                'index, nofollow',
                'noindex, nofollow'
            ])
                ->default('index, follow');
            $table->enum('changefreq', [
                'always',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'never'
            ])
                ->default('monthly');
            $table->float('priority', 2, 1)
                ->default(0.5);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('url_alias');
    }
}

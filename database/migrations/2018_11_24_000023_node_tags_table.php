<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Structure\Tag;
use App\Models\Seo\TmpMetaTags;
use App\Models\Seo\SearchIndex;
use App\Models\Components\FilesReference;
use App\Models\Seo\UrlAlias;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NodeTagsTable extends Migration
{
    protected $table;
    protected $pages;

    public function up()
    {
        Schema::create('node_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->boolean('status')
                ->default(1);
            $table->tinyInteger('sort')
                ->default(0);
            $table->timestamps();
        });
        Schema::create('node_taggables', function (Blueprint $table) {
            $table->integer('node_tag_id')
                ->unsigned()
                ->nullable();
            $table->morphs('model');
            $table->foreign('node_tag_id')
                ->references('id')
                ->on('node_tags')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (Schema::hasTable('node_tags')) {
            Schema::table('node_taggables', function (Blueprint $table) {
                $table->dropForeign('node_taggables_node_tag_id_foreign');
                $table->drop();
            });
            Schema::table('node_tags', function (Blueprint $table) {
                $table->drop();
            });
        }
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files_relations', function (Blueprint $table) {
            $table->integer('files_id')->unsigned();
            $table->integer('relation_id')->unsigned();
            $table->string('relation_type', 255);
            foreach(config('app.locales', [config('app.fallback_locale', 'en')]) as $locale){
                $table->string('title_'.$locale, 255)->nullable();
                $table->text('description_'.$locale)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('files_relations');
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('directories_id')->unsigned()->default(1)->index();
            $table->string('filename', 255)->index();
            $table->string('extension', 10)->index();
            $table->string('mime_type', 255)->index();
            $table->string('author', 255)->nullable();
            $table->text('exif_data')->nullable();
            $table->boolean('viewable')->default(1)->index();
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
        Schema::drop('files');
    }
}

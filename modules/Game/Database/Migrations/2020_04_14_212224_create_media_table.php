<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->string('path');
            $table->tinyInteger('type');
            $table->tinyInteger('mime_type');
            $table->tinyInteger('format');
            $table->tinyInteger('size');
            $table->string('preview')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('media_relations', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id');
            $table->unsignedSmallInteger('entity_type');
            $table->foreignId('media_id')->constrained('medias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medias');
        Schema::dropIfExists('media_relations');
    }
};

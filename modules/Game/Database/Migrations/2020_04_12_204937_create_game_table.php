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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->integer('rawg_id')->nullable();
            $table->unsignedBigInteger('esrb_rating_id')->nullable();
            $table->foreign('esrb_rating_id')->on('esrb_ratings')->references('id');
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->smallInteger('metacritic')->default(0)->nullable();
            $table->string('metacritic_url')->nullable();
            $table->string('background_image')->nullable();
            $table->smallInteger('rating')->default(0);
            $table->text('ratings')->nullable();
            $table->smallInteger('rating_top')->default(0);
            $table->smallInteger('playtime')->default(0);
            $table->mediumInteger('suggestions_count')->default(0);
            $table->string('image')->nullable();
            $table->string('website')->nullable();
            $table->string('release_time')->nullable();
            $table->integer('use_count')->default(0);
            $table->boolean('is_global')->default(true);
            $table->text('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
};

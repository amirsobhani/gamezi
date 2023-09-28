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
        Schema::create('game_platforms', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->on('games')->references('id')->onDelete('cascade');
            $table->unsignedBigInteger('platform_id');
            $table->foreign('platform_id')->on('platforms')->references('id')->onDelete('cascade');
            $table->primary(['game_id', 'platform_id']);
            $table->text('recommended')->nullable();
            $table->text('requirements_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_platforms');
    }
};

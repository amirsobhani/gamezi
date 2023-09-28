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
        Schema::create('game_developers', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->on('games')->references('id')->onDelete('cascade');
            $table->unsignedBigInteger('developer_id');
            $table->foreign('developer_id')->on('developers')->references('id')->onDelete('cascade');
            $table->primary(['game_id', 'developer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_developers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public static function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('user_id');
            $table->string('refresh_token_hash')->unique();
            $table->string('refresh_token_hash_old')->nullable();
            $table->timestamp('refresh_token_expires');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public static function down()
    {
        Schema::dropIfExists('sessions');
    }
}

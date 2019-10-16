<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public static function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->boolean('verified')->default(true);
            $table->string('reset_password_token', config('tokens.jwt_token.length'))->nullable()->unique();
            $table->string('verify_email_token', config('tokens.jwt_token.length'))->nullable()->unique();
            $table->timestamps();
        });
    }

    public static function down()
    {
        Schema::dropIfExists('users');
    }
}

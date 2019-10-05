<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    public static function up()
    {
        $reset_token_length = config('tokens.reset_password_token.length');
        $verify_token_length = config('tokens.verify_mail_token.length');

        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('reset_password_token', $reset_token_length)->nullable()->unique();
            $table->string('verify_email_token', $verify_token_length)->nullable()->unique();
            $table->timestamps();
        });
    }

    public static function down()
    {
        Schema::dropIfExists('users');
    }
}

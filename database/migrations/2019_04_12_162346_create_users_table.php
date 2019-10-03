<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    public static function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('reset_password_token', 128)->nullable()->unique();
            $table->string('verify_email_token', 128)->nullable()->unique();
            $table->timestamps();
        });
    }

    public static function down()
    {
        Schema::dropIfExists('users');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('dash_users', function (Blueprint $table) {
            $table->bigIncrements('id');  // bigint unsigned, auto_increment
            $table->string('name')->nullable();  // varchar(255), nullable
            $table->string('email')->unique();  // varchar(255), unique, not nullable
            $table->timestamp('email_verified_at')->nullable();  // timestamp, nullable
            $table->string('password');  // varchar(255), not nullable
            $table->string('remember_token', 100)->nullable();  // varchar(100), nullable
            $table->timestamps();  // created_at and updated_at, both are timestamp and nullable by default
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dash_users');
    }
};

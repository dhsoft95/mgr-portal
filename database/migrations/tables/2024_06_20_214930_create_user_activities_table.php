<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->bigIncrements('id');  // bigint unsigned, auto_increment, primary key
            $table->unsignedInteger('user_id')->nullable();  // int unsigned, nullable
            $table->string('url', 255);  // varchar(255), not nullable
            $table->timestamp('created_at')->nullable();  // timestamp, nullable
            $table->timestamp('updated_at')->nullable();  // timestamp, nullable
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

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
        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();  // char(36), primary key
            $table->string('type', 255)->nullable();  // varchar(255), nullable
            $table->string('notifiable_type', 255)->nullable();  // varchar(255), nullable
            $table->unsignedBigInteger('notifiable_id')->nullable();  // bigint unsigned, nullable
            $table->text('data')->nullable();  // text, nullable
            $table->timestamp('read_at')->nullable();  // timestamp, nullable
            $table->timestamps();  // created_at and updated_at, both are timestamp and nullable by default
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

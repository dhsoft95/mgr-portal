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
        Schema::connection('second_database')->create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_id');
            $table->text('user_message');
            $table->text('bot_response');
            $table->string('type');
            $table->json('conversation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('second_database')->dropIfExists('user_interactions');
    }
};

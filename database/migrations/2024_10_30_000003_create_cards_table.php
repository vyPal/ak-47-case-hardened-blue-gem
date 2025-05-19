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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->enum('status', ['draft', 'voting', 'minted'])->default('draft');
            $table->integer('rarity')->nullable();
            $table->integer('hp')->nullable();
            $table->integer('strength')->nullable();
            $table->integer('defense')->nullable();
            $table->float('balance_score')->default(1.0);
            $table->integer('upload_week');
            $table->integer('upload_year');
            $table->integer('minted_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('duration', 8);
            $table->string('model_name', 120);
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->enum('source_type', ['local', 'url']);
            $table->string('source_path', 2048);
            $table->string('thumbnail_url', 2048)->nullable();
            $table->timestamps();
            $table->index(['category_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('videos'); }
};

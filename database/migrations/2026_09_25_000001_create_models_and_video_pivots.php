<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->timestamps();
        });

        Schema::create('category_video', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'video_id']);
        });

        Schema::create('model_video', function (Blueprint $table) {
            $table->foreignId('model_name_id')->constrained('models')->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->primary(['model_name_id', 'video_id']);
        });

        foreach (DB::table('videos')->get(['id', 'category_id', 'model_name']) as $video) {
            $category = $video->category_id ? [['category_id' => $video->category_id, 'video_id' => $video->id]] : [];
            if ($category) DB::table('category_video')->insert($category);
            if (trim((string) $video->model_name) !== '') {
                $modelId = DB::table('models')->where('name', trim($video->model_name))->value('id');
                if (!$modelId) {
                    $modelId = DB::table('models')->insertGetId([
                        'name' => trim($video->model_name),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('model_video')->insert(['model_name_id' => $modelId, 'video_id' => $video->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('model_video');
        Schema::dropIfExists('category_video');
        Schema::dropIfExists('models');
    }
};

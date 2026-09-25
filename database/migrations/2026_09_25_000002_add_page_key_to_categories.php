<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Category;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('page_key', 40)->nullable()->unique()->after('slug');
        });

        foreach ([
            'fav' => 'Fav',
            'edukasi' => 'Edukasi',
            'tutorial' => 'Tutorial',
            'music' => 'Music',
            'lucu' => 'Lucu',
        ] as $key => $name) {
            $category = Category::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)],
            );
            $category->update(['page_key' => $key]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['page_key']);
            $table->dropColumn('page_key');
        });
    }
};

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:categories,name']]);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(5))]);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        if ($category->videos()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai video. Ubah kategori video terlebih dahulu.']);
        }
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}

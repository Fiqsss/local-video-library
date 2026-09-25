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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
            'page_key' => ['nullable', 'string', 'max:40', 'unique:categories,page_key'],
        ]);
        Category::create([
            'name' => trim($data['name']),
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(5)),
            'page_key' => $data['page_key'] ?: null,
        ]);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        if ($category->videos()->exists() || $category->taggedVideos()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai video. Ubah kategori video terlebih dahulu.']);
        }
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}

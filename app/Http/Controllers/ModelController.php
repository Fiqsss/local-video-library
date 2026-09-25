<?php

namespace App\Http\Controllers;

use App\Models\ModelName;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:models,name']]);
        ModelName::create(['name' => trim($data['name'])]);

        return back()->with('success', 'Model berhasil ditambahkan.');
    }

    public function destroy(ModelName $model)
    {
        if ($model->videos()->exists()) {
            return back()->withErrors(['model' => 'Model masih dipakai video. Ubah video terlebih dahulu.']);
        }
        $model->delete();

        return back()->with('success', 'Model berhasil dihapus.');
    }
}

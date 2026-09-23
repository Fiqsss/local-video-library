<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CategoryController;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [VideoController::class, 'admin'])->name('videos.index');
    Route::get('/video-files', [VideoController::class, 'browseFiles'])->name('videos.files');
    Route::get('/video-preview', [VideoController::class, 'previewFile'])->name('videos.preview');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

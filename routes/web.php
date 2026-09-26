<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ModelController;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');
Route::get('/videos/{video}/thumbnail', [VideoController::class, 'thumbnail'])->name('videos.thumbnail');
Route::get('/videos/{video}/metadata', [VideoController::class, 'metadata'])->name('videos.metadata');
Route::get('/videos/{video}/related', [VideoController::class, 'related'])->name('videos.related');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [VideoController::class, 'admin'])->name('videos.index');
    Route::get('/duplicates', [VideoController::class, 'duplicates'])->name('videos.duplicates');
    Route::delete('/duplicates', [VideoController::class, 'deleteDuplicates'])->name('videos.duplicates.delete');
    Route::delete('/duplicates/recommended', [VideoController::class, 'deleteRecommendedDuplicate'])->name('videos.duplicates.recommended');
    Route::delete('/duplicates/recommended-all', [VideoController::class, 'deleteAllRecommendedDuplicates'])->name('videos.duplicates.recommended-all');
    Route::get('/video-files', [VideoController::class, 'browseFiles'])->name('videos.files');
    Route::get('/video-preview', [VideoController::class, 'previewFile'])->name('videos.preview');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    Route::delete('/videos-bulk', [VideoController::class, 'bulkDestroy'])->name('videos.bulk-destroy');

    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/models', [ModelController::class, 'store'])->name('models.store');
    Route::delete('/models/{model}', [ModelController::class, 'destroy'])->name('models.destroy');
});

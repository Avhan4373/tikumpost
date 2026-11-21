<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\AuthorController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ReelController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\TagController;
use App\Http\Middleware\EnsureSiteIsLive;
use Illuminate\Support\Facades\Route;

// ---- Publik (ditutup saat mode perawatan aktif) ----
Route::middleware(EnsureSiteIsLive::class)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/berita/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::post('/berita/{article}/komentar', [ArticleController::class, 'comment'])
        ->middleware('throttle:10,1')->name('articles.comment');
    Route::get('/kategori/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/tag/{tag}', [TagController::class, 'show'])->name('tags.show');
    Route::get('/penulis/{author}', [AuthorController::class, 'show'])->name('authors.show');
    Route::get('/cari', [SearchController::class, 'index'])->name('search');
    Route::get('/halaman/{slug}', [PageController::class, 'show'])->name('pages.show');
    Route::get('/tikumreals', [ReelController::class, 'index'])->name('reels.index');
    Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
});

// ---- Autentikasi Pembaca ----
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login']);
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---- Area Admin ----
require __DIR__.'/admin.php';

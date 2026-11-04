<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



//入力ページ表示
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
//バリデーションを通過した後確認ページに移る
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
//確認ページから送信したのちサンクスページへ
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
//サンクスページ表示
Route::get('/thanks', function () {
    return view('contact.thanks');
})->name('contact.thanks');

Route::middleware('auth')->group(function () {


    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{tag}/edit', [TagController::class, 'edit']);
    Route::patch('/tags/{tag}', [TagController::class, 'update']);
});

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
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');

Route::get('/thanks', function () {
    return view('contact.thanks');
})->name('contact.thanks');
Route::get('/', [ContactController::class, 'index'])->name('contact.index');

Route::middleware('auth')->group(function () {


    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
    Route::put('/admin/tags/{tag}', [TagController::class, 'update'])->name('tags.update');

    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->name('admin.contacts.destroy');
});

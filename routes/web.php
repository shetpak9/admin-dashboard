<?php

use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('index');
Route::get('/add', [ProductController::class, 'create'])->name('create');
Route::post('/add', [ProductController::class, 'store'])->name('store');
Route::get('/edit/{product}', [ProductController::class, 'edit'])->name('edit');
Route::patch('/edit/{product}', [ProductController::class, 'update'])->name('update');
Route::delete('/delete/{product}', [ProductController::class, 'destroy'])->name('destroy');




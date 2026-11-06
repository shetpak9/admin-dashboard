<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/session', [SessionController::class, 'store'])->middleware('throttle:5,1')->name('session.store');
Route::delete('/session', [SessionController::class, 'destroy'])->name('session.destroy');

Route::middleware('firebase.session')->group(function (): void {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/add', [ProductController::class, 'create'])->name('create');
    Route::post('/add', [ProductController::class, 'store'])->name('store');
    Route::get('/edit/{product}', [ProductController::class, 'edit'])->name('edit');
    Route::patch('/edit/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/delete/{product}', [ProductController::class, 'destroy'])->name('destroy');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{uid}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{uid}/disable', [UserController::class, 'disable'])->name('users.disable');
    Route::post('/users/{uid}/enable', [UserController::class, 'enable'])->name('users.enable');
    Route::post('/users/{uid}/revoke-sessions', [UserController::class, 'revokeSessions'])->name('users.revoke-sessions');
    Route::get('/users/{uid}/delete', [UserController::class, 'confirmDelete'])->name('users.delete.confirm');
    Route::delete('/users/{uid}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/audit-log', [UserController::class, 'auditLog'])->name('audit-log.index');
});

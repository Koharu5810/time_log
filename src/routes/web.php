<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberAuthController;
use App\Http\Controllers\AttendanceCreateController;

// 会員登録画面
Route::get('/register', [MemberAuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('register.show');
Route::post('/register', [MemberAuthController::class, 'register'])->withoutMiddleware(['auth'])->name('register');
// ログイン画面
Route::get('/login', [MemberAuthController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [MemberAuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceCreateController::class, 'index']);
});

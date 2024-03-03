<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest')
                ->name('register');

Route::post('/login', [AuthenticationController::class, 'store'])
                ->middleware('guest')
                ->name('login');

Route::post('/logout', [AuthenticationController::class, 'destroy'])
                ->middleware('auth:sanctum')
                ->name('logout');

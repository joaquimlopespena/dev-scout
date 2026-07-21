<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\FirstPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DeveloperDiscoveryController;
use App\Http\Controllers\DeveloperSyncController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/first-access/password', [FirstPasswordController::class, 'edit'])->name('password.first.edit');
    Route::put('/first-access/password', [FirstPasswordController::class, 'update'])->name('password.first.update');
});
Route::middleware(['auth', 'password.changed', 'tenant'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/developers', [DeveloperController::class, 'index'])->name('developers.index');
    Route::get('/developers/discover', DeveloperDiscoveryController::class)->middleware('throttle:github-sync')->name('developers.discover');
    Route::post('/developers/sync', DeveloperSyncController::class)->middleware('throttle:github-sync')->name('developers.sync');
    Route::get('/developers/compare', [DeveloperController::class, 'compare'])->name('developers.compare');
    Route::get('/developers/{developer}', [DeveloperController::class, 'show'])->name('developers.show');
    Route::patch('/developers/{developer}/recruitment', [RecruitmentController::class, 'update'])->name('developers.recruitment.update');
    Route::get('/pipeline', [RecruitmentController::class, 'index'])->name('pipeline.index');
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/members', [TeamController::class, 'store'])->name('team.members.store');
    Route::patch('/team/members/{membership}', [TeamController::class, 'update'])->whereNumber('membership')->name('team.members.update');
    Route::delete('/team/members/{membership}', [TeamController::class, 'destroy'])->whereNumber('membership')->name('team.members.destroy');
    Route::get('/audit', AuditLogController::class)->name('audit.index');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

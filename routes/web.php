<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BaganController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LiveScoreController;
use App\Http\Controllers\Panitia\BannerController;
use App\Http\Controllers\Panitia\DashboardController;
use App\Http\Controllers\Panitia\GameController;
use App\Http\Controllers\Panitia\GroupController;
use App\Http\Controllers\Panitia\MatchController;
use App\Http\Controllers\Panitia\ParticipantController;
use App\Http\Controllers\Panitia\ScoreController;
use App\Http\Controllers\Panitia\UserController;
use App\Http\Controllers\Pic\ScheduleController as PicScheduleController;
use App\Http\Controllers\Pic\TeamProfileController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TeamsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/live-score', LiveScoreController::class)->name('live-score');
Route::get('/jadwal', ScheduleController::class)->name('jadwal');
Route::get('/games', GamesController::class)->name('games');
Route::get('/tim', [TeamsController::class, 'index'])->name('tim');
Route::get('/tim/{team:slug}', [TeamsController::class, 'show'])->name('tim.show');
Route::get('/bagan', BaganController::class)->name('bagan');
Route::get('/leaderboard', LeaderboardController::class)->name('leaderboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'pic'])->prefix('akun')->name('pic.')->group(function () {
    Route::get('/', fn () => redirect()->route('pic.jadwal'))->name('home');
    Route::get('/jadwal', [PicScheduleController::class, 'index'])->name('jadwal');
    Route::get('/profil', [TeamProfileController::class, 'show'])->name('profil');
    Route::get('/roster', [RosterController::class, 'edit'])->name('rosters.edit');
    Route::put('/roster', [RosterController::class, 'update'])->name('rosters.update');
});

Route::middleware(['auth', 'panitia'])->prefix('panitia')->name('panitia.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/banner', [BannerController::class, 'edit'])->name('banners.edit');
    Route::put('/banner', [BannerController::class, 'update'])->name('banners.update');

    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::resource('grup', GroupController::class)->parameters(['grup' => 'team'])->except(['show']);
    Route::resource('games', GameController::class)->except(['show']);
    Route::resource('matches', MatchController::class)->except(['show']);

    Route::get('/roster', [RosterController::class, 'edit'])->name('rosters.edit');
    Route::put('/roster', [RosterController::class, 'update'])->name('rosters.update');

    Route::get('/peserta', [ParticipantController::class, 'index'])->name('participants.index');
    Route::post('/peserta', [ParticipantController::class, 'store'])->name('participants.store');
    Route::put('/peserta/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/peserta/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

    Route::get('/skor', [ScoreController::class, 'index'])->name('scores.index');
    Route::get('/skor/{gameMatch}', [ScoreController::class, 'edit'])->name('scores.edit');
    Route::put('/skor/{gameMatch}', [ScoreController::class, 'update'])->name('scores.update');
    Route::post('/skor/{gameMatch}/selesai', [ScoreController::class, 'finish'])->name('scores.finish');
    Route::post('/skor/{gameMatch}/babak', [ScoreController::class, 'addPeriod'])->name('scores.period');
});

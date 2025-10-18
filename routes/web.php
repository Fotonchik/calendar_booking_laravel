<?php
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('home');
Route::post('/get-week-availability', [BookingController::class, 'getWeekAvailability'])->name('week.availability');
Route::post('/get-slots', [BookingController::class, 'getSlots'])->name('get.slots');
Route::post('/book', [BookingController::class, 'store'])->name('book.store');
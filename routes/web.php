<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThingController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\AuthController;

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

//Things - поддерживаем как session, так и token аутентификацию через Sanctum
Route::controller(ThingController::class)->prefix('things')->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index')->name('things.index');
    Route::get('/my', 'myThings')->name('things.my');
    Route::get('/repair', 'repairThings')->name('things.repair');
    Route::get('/work', 'workThings')->name('things.work');
    Route::get('/used', 'usedThings')->name('things.used');
    Route::get('/all', 'allThings')->name('things.all');
    Route::get('/create', 'create')->name('things.create');
    Route::post('/', 'store')->name('things.store');
    Route::get('/{thing}', 'show')->name('things.show');
    Route::get('/{thing}/transfer', 'showTransferForm')->name('things.transfer');
    Route::post('/{thing}/transfer', 'transfer')->name('things.transfer.store');
    Route::get('/{thing}/edit', 'edit')->name('things.edit');
    Route::put('/{thing}', 'update')->name('things.update');
    Route::delete('/{thing}', 'destroy')->name('things.destroy');
    
    // Описания вещи
    Route::post('/{thing}/descriptions', [\App\Http\Controllers\ThingDescriptionController::class, 'store'])->name('things.descriptions.store');
    Route::post('/{thing}/descriptions/{description}/set-current', [\App\Http\Controllers\ThingDescriptionController::class, 'setCurrent'])->name('things.descriptions.set-current');
});

//Places - поддерживаем как session, так и token аутентификацию через Sanctum
Route::resource('places', PlaceController::class)->middleware('auth:sanctum');

//Archived Things - поддерживаем как session, так и token аутентификацию через Sanctum
Route::prefix('archived')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [\App\Http\Controllers\ArchivedThingController::class, 'index'])->name('archived.index');
    Route::post('/{archivedThing}/restore', [\App\Http\Controllers\ArchivedThingController::class, 'restore'])->name('archived.restore');
});

//Auth
Route::get('/auth/signin', [AuthController::class, 'signin'])->name('signin');
Route::post('/auth/registr', [AuthController::class, 'registr'])->name('registr');
Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');

//Main
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('things.index');
    }
    return redirect()->route('login');
});

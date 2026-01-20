<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ThingController;
use App\Http\Controllers\API\PlaceController;
use App\Http\Controllers\API\ArchivedThingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth API
Route::prefix('auth')->group(function () {
    Route::post('/registr', [AuthController::class, 'registr'])->name('api.registr');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('api.login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.user');
    });
});

// Things API
Route::middleware('auth:sanctum')->prefix('things')->group(function () {
    Route::get('/', [ThingController::class, 'index'])->name('api.things.index');
    Route::get('/my', [ThingController::class, 'myThings'])->name('api.things.my');
    Route::get('/repair', [ThingController::class, 'repairThings'])->name('api.things.repair');
    Route::get('/work', [ThingController::class, 'workThings'])->name('api.things.work');
    Route::get('/used', [ThingController::class, 'usedThings'])->name('api.things.used');
    Route::get('/all', [ThingController::class, 'allThings'])->name('api.things.all');
    Route::post('/', [ThingController::class, 'store'])->name('api.things.store');
    Route::get('/{thing}', [ThingController::class, 'show'])->name('api.things.show');
    Route::post('/{thing}/transfer', [ThingController::class, 'transfer'])->name('api.things.transfer');
    Route::put('/{thing}', [ThingController::class, 'update'])->name('api.things.update');
    Route::delete('/{thing}', [ThingController::class, 'destroy'])->name('api.things.destroy');
    
    // Описания вещей
    Route::post('/{thing}/descriptions', [ThingController::class, 'storeDescription'])->name('api.things.descriptions.store');
    Route::post('/{thing}/descriptions/{description}/set-current', [ThingController::class, 'setCurrentDescription'])->name('api.things.descriptions.set-current');
});

// Places API
Route::middleware('auth:sanctum')->prefix('places')->group(function () {
    Route::get('/', [PlaceController::class, 'index'])->name('api.places.index');
    Route::post('/', [PlaceController::class, 'store'])->name('api.places.store');
    Route::get('/{place}', [PlaceController::class, 'show'])->name('api.places.show');
    Route::put('/{place}', [PlaceController::class, 'update'])->name('api.places.update');
    Route::delete('/{place}', [PlaceController::class, 'destroy'])->name('api.places.destroy');
});

// Archived Things API
Route::middleware('auth:sanctum')->prefix('archived')->group(function () {
    Route::get('/', [ArchivedThingController::class, 'index'])->name('api.archived.index');
    Route::post('/{archivedThing}/restore', [ArchivedThingController::class, 'restore'])->name('api.archived.restore');
});

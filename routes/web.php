<?php /** @noinspection ALL */

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/resto-tables', [\App\Http\Controllers\RestaurantTablesController::class, 'index'])->name('resto.tables');

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/empty-tables-count', [\App\Http\Controllers\RestaurantTablesController::class, 'fetchEmptyTablesCount'])->name('api.empty-tables-count');
    Route::get('/busy-tables-count', [\App\Http\Controllers\RestaurantTablesController::class, 'fetchBusyTablesCount'])->name('api.busy-tables-count');
    Route::get('/all-table-counts', [\App\Http\Controllers\RestaurantTablesController::class, 'fetchAllTableCounts'])->name('api.all-table-counts');
    Route::get('/empty-tables', [\App\Http\Controllers\RestaurantTablesController::class, 'fetchEmptyTables'])->name('api.empty-tables');
    Route::get('/busy-tables', [\App\Http\Controllers\RestaurantTablesController::class, 'fetchBusyTables'])->name('api.busy-tables');
    Route::post('/assign-table', [\App\Http\Controllers\RestaurantTablesController::class, 'assignTable'])->name('api.assign-table');
    Route::post('/release-table', [\App\Http\Controllers\RestaurantTablesController::class, 'releaseTable'])->name('api.release-table');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

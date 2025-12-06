<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;

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

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/queue', [HomeController::class, 'publicQueue'])->name('public.queue');

// Authentication Routes (from Breeze)
require __DIR__.'/auth.php';

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'role:SUPER_ADMIN,ADMIN,MANAGER'])->prefix('admin')->group(function () {
    // Will add later
});

// Loket Routes
Route::middleware(['auth', 'role:LOKET_STAFF'])->prefix('loket')->group(function () {
    // Will add later
});

// Driver Routes
Route::middleware(['auth', 'role:DRIVER'])->prefix('driver')->group(function () {
    // Will add later
});

Route::get('/debug-loket', function() {
    $user = \App\Models\User::where('email', 'budi@pelabuhan.com')->first();
    auth()->login($user);
    
    $counter = $user->counter;
    
    echo "<h1>Debug Counter Data</h1>";
    echo "<pre>";
    echo "Counter ID: " . $counter->id . "\n";
    echo "Counter Name: " . $counter->name . "\n";
    echo "Service Types (raw): " . $counter->getRawOriginal('service_types') . "\n";
    echo "Service Types (casted): ";
    print_r($counter->service_types);
    echo "\nType: " . gettype($counter->service_types) . "\n";
    echo "Is array? " . (is_array($counter->service_types) ? 'YES' : 'NO') . "\n";
    echo "Is string? " . (is_string($counter->service_types) ? 'YES' : 'NO') . "\n";
    
    // Test getServiceTypesSafe
    echo "\nUsing getServiceTypesSafe:\n";
    print_r($counter->getServiceTypesSafe());
    echo "</pre>";
    
    exit;
});
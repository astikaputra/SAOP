<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\Api\QueueApiController;

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
    // Dashboard berdasarkan role
    Route::get('/dashboard', function() {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        switch ($user->role) {
            case 'LOKET_STAFF':
                return redirect()->route('loket.index');
            case 'ADMIN':
                return redirect()->route('admin.dashboard');
            case 'DRIVER':
                return redirect()->route('driver.dashboard');
            default:
                return view('dashboard');
        }
    })->name('dashboard');
});

// Admin Routes
Route::middleware(['auth', 'role:SUPER_ADMIN,ADMIN,MANAGER'])->prefix('admin')->group(function () {
    // Will add later
});

    // Queue routes
    Route::prefix('queue')->group(function () {
        Route::get('/create', [QueueController::class, 'create'])->name('queue.create');
        Route::post('/store', [QueueController::class, 'store'])->name('queue.store');
        Route::get('/tickets', [QueueController::class, 'index'])->name('queue.tickets');
        Route::get('/ticket/{id}', [QueueController::class, 'show'])->name('queue.ticket.show');
        Route::get('/print/{id}', [QueueController::class, 'printTicket'])->name('queue.print.ticket');
    });
    

// Loket Routes
Route::middleware(['auth'])->prefix('loket')->group(function () {
    Route::get('/', [LoketController::class, 'index'])->name('loket.index');
    Route::post('/update-status', [LoketController::class, 'updateStatus'])->name('loket.update-status');
    
    Route::post('/call-next', [LoketController::class, 'callNext'])->name('loket.call-next');
    Route::post('/complete-current', [LoketController::class, 'completeCurrent'])->name('loket.complete-current');
    Route::post('/skip-current', [LoketController::class, 'skipCurrent'])->name('loket.skip-current');
    
    Route::get('/queue-data', [LoketController::class, 'getQueueData'])->name('loket.queue-data');
    
    // Tambahan routes
    Route::post('/call-specific/{id}', [LoketController::class, 'callSpecific'])->name('loket.call-specific');
    Route::post('/skip-ticket/{id}', [LoketController::class, 'skipTicket'])->name('loket.skip-ticket');
    Route::post('/recall-ticket/{id}', [LoketController::class, 'recallTicket'])->name('loket.recall-ticket');
});

// =========== DRIVER ROUTES ===========
Route::middleware(['auth', 'check.role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [DriverController::class, 'tasks'])->name('tasks');
    Route::get('/tasks/{queueTicket}', [DriverController::class, 'showTask'])->name('tasks.show');
    Route::put('/tasks/{queueTicket}/update-status', [DriverController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('/history', [DriverController::class, 'history'])->name('history');
    Route::get('/profile', [DriverController::class, 'profile'])->name('profile');
    Route::put('/profile', [DriverController::class, 'updateProfile'])->name('profile.update');
});

// routes/web.php - tambahkan route debug
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

// routes/web.php - tambahkan route debug
Route::get('/loket/debug', function() {
    return view('loket.debug');
})->middleware('auth');

// routes/web.php
Route::get('/loket/test-relationship', function() {
    $user = \App\Models\User::find(3);
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'counter_id' => $user->counter_id
        ],
        'counter' => $user->counter ? [
            'id' => $user->counter->id,
            'name' => $user->counter->name,
            'user_id' => $user->counter->user_id
        ] : null,
        'relationship_works' => $user->counter !== null
    ]);
})->middleware('auth');

// routes/web.php - tambahkan route test

Route::get('/test-loket-simple', function() {
    $user = Auth::user();
    
    if (!$user) {
        return 'Not logged in';
    }
    
    if ($user->role !== 'LOKET_STAFF') {
        return 'Not LOKET_STAFF role';
    }
    
    // Coba instantiate controller
    try {
        $queueService = new \App\Services\QueueService();
        $controller = new \App\Http\Controllers\LoketController($queueService);
        
        return 'Controller instantiated successfully';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine();
    }
});

// routes/web.php - tambahkan route debug

Route::get('/debug/queue-create', function() {
    $user = Auth::user();
    $counter = $user->counter;
    $serviceTypes = $counter->getServiceTypesSafe();
    
    $servicesByType = \App\Models\Service::whereIn('type', $serviceTypes)
        ->where('is_active', true)
        ->orderBy('type')
        ->orderBy('base_price')
        ->get()
        ->groupBy('type');
    
    return response()->json([
        'user' => $user->only(['id', 'name', 'role']),
        'counter' => $counter,
        'service_types' => $serviceTypes,
        'services_by_type' => $servicesByType,
        'services_count' => $servicesByType->map(function($services) {
            return count($services);
        })
    ]);
})->middleware('auth');

Route::middleware(['api'])->group(function () {
    Route::get('/queue/estimation', [QueueApiController::class, 'getQueueEstimation']);
    Route::get('/services', [QueueApiController::class, 'getServices']);
});

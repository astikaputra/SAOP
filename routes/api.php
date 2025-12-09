<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueTicketController;
use App\Http\Controllers\Api\QueueApiController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\QueueEstimationController;

//Dengan sanctum authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/queue/estimation', [QueueEstimationController::class, 'estimation']);
});
// API Routes for queue system
Route::middleware(['api'])->prefix('api')->group(function () {
    // Services API
    Route::get('/services', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\Service::where('is_active', true);
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        $services = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    });
    
    // Queue estimation API
    Route::get('/queue/estimation', [QueueApiController::class, 'getQueueEstimation']);
    
    // Current queues API (for display boards)
    Route::get('/queue/current', [QueueTicketController::class, 'getCurrentTickets']);
    Route::get('/queue/waiting', [QueueTicketController::class, 'getWaitingTickets']);
    
    // Statistics API
    Route::get('/queue/stats', [QueueTicketController::class, 'getStats']);
    
    // Take queue API (for public/kiosk)
    Route::post('/queue/take', [QueueTicketController::class, 'store']);
});
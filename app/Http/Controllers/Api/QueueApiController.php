<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\QueueTicket;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QueueApiController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Get queue estimation for a service
     */
    public function getQueueEstimation(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id'
        ]);
        
        $service = Service::find($request->service_id);
        
        // Get last queue number for today
        $lastTicket = QueueTicket::where('service_id', $service->id)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('queue_number', 'desc')
            ->first();
        
        $nextQueueNumber = ($lastTicket ? $lastTicket->queue_number : 0) + 1;
        
        // Count waiting tickets
        $waitingCount = QueueTicket::where('service_id', $service->id)
            ->where('status', 'WAITING')
            ->count();
        
        // Estimate wait time (5 minutes per ticket)
        $estimatedWaitMinutes = $waitingCount * 5;
        
        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'queue_number' => $nextQueueNumber,
                'waiting_count' => $waitingCount,
                'estimated_wait_minutes' => $estimatedWaitMinutes,
                'estimated_price' => $service->base_price,
                'price_per_km' => $service->price_per_km
            ]
        ]);
    }

    /**
     * Get services by type
     */
    public function getServices(Request $request)
    {
        $query = Service::where('is_active', true);
        if ($request->type) {
            $query->where('type', $request->type);
        }   
        $services = $query->get();
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
}

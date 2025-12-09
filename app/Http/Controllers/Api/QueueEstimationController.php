<?php

// app/Http/Controllers/Api/QueueEstimationController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueEstimationController extends Controller
{
    public function estimation(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id'
        ]);
        
        $service = Service::find($request->service_id);
        
        // Hitung antrian aktif untuk layanan ini
        $activeQueues = Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->count();
        
        // Generate nomor antrian berikutnya
        $queueNumber = strtoupper(substr($service->type, 0, 1)) . 
                      str_pad($activeQueues + 1, 3, '0', STR_PAD_LEFT);
        
        // Estimasi waktu tunggu (rata-rata 5 menit per antrian)
        $estimatedWaitMinutes = ($activeQueues + 1) * 5;
        
        return response()->json([
            'success' => true,
            'data' => [
                'queue_number' => $queueNumber,
                'estimated_wait_minutes' => $estimatedWaitMinutes,
                'current_active_queues' => $activeQueues
            ]
        ]);
    }
}
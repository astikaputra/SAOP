<?php
// app/Http\Controllers/QueueController.php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Counter;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Display form untuk membuat antrian baru
     */
    // public function create()
    // {
    //     $user = Auth::user();
        
    //     // Role check
    //     if ($user->role !== 'LOKET_STAFF') {
    //         return redirect()->route('dashboard')
    //             ->with('error', 'Hanya staff loket yang dapat membuat antrian.');
    //     }
        
    //     // Get services berdasarkan counter user
    //     $counter = $user->counter;
        
    //     if (!$counter) {
    //         return redirect()->route('loket.index')
    //             ->with('error', 'Anda tidak ditugaskan ke loket manapun.');
    //     }
        
    //     $serviceTypes = $counter->getServiceTypesSafe();
        
    //     // Group services by type
    //     $servicesByType = Service::whereIn('type', $serviceTypes)
    //         ->where('is_active', true)
    //         ->orderBy('type')
    //         ->orderBy('base_price')
    //         ->get()
    //         ->groupBy('type');
        
    //     // Get available counters (untuk admin mungkin bisa pilih counter lain)
    //     $counters = Counter::where('status', 'ACTIVE')
    //         ->where('is_active', true)
    //         ->get();
        
    //     return view('queue.create', compact('servicesByType', 'counter', 'counters'));
    // }

    // Di QueueController.php - method create()

    public function create()
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return redirect()->route('dashboard')
                ->with('error', 'Hanya staff loket yang dapat membuat antrian.');
        }
        
        // Get services berdasarkan counter user
        $counter = $user->counter;
        
        if (!$counter) {
            return redirect()->route('loket.index')
                ->with('error', 'Anda tidak ditugaskan ke loket manapun.');
        }
        
        $serviceTypes = $counter->getServiceTypesSafe();
        
        // Debug: Log service types
        \Log::info('Service types for counter', [
            'counter_id' => $counter->id,
            'service_types' => $serviceTypes
        ]);
        
        // Group services by type
        $services = Service::whereIn('type', $serviceTypes)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('base_price')
            ->get();
        
        $servicesByType = $services->groupBy('type');
        
        // Jika tidak ada services, buat default
        if ($servicesByType->isEmpty()) {
            \Log::warning('No services found for types', ['types' => $serviceTypes]);
            
            // Buat default service untuk testing
            $defaultService = Service::firstOrCreate(
                ['type' => 'PICKUP_TRANSPORTASI', 'sub_type' => 'Drop'],
                [
                    'name' => 'Pickup Drop',
                    'description' => 'Layanan antar jemput ke tujuan',
                    'base_price' => 50000,
                    'price_per_km' => 5000,
                    'capacity' => 4,
                    'is_active' => true
                ]
            );
            
            $servicesByType = collect(['PICKUP_TRANSPORTASI' => collect([$defaultService])]);
        }
        
        // Get available counters
        $counters = Counter::where('status', 'ACTIVE')
            ->where('is_active', true)
            ->get();
        
        return view('queue.create', compact('servicesByType', 'counter', 'counters'));
    }
    
    /**
     * Store new queue ticket
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Validate request
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'passenger_count' => 'required|integer|min:1',
            'pickup_location' => 'nullable|string',
            'destination' => 'nullable|string',
            'distance_km' => 'nullable|numeric|min:0',
            'telegram_chat_id' => 'nullable|string',
            'notes' => 'nullable|string',
            'priority' => 'nullable|boolean',
            'counter_id' => 'required|exists:counters,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $service = Service::findOrFail($request->service_id);
            $counter = Counter::findOrFail($request->counter_id);
            
            // Generate ticket number
            $date = now()->format('Ymd');
            $lastTicket = QueueTicket::whereDate('created_at', now()->toDateString())
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastTicket ? intval(substr($lastTicket->ticket_number, -3)) + 1 : 1;
            $ticketNumber = 'TKT-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
            // Generate queue number
            $queueNumber = $this->generateQueueNumber($service, $counter);
            
            // Calculate total price
            $totalPrice = $service->base_price;
            if ($service->price_per_km && $request->distance_km) {
                $totalPrice += $service->price_per_km * $request->distance_km;
            }
            
            // Create queue ticket
            $queueTicket = QueueTicket::create([
                'ticket_number' => $ticketNumber,
                'queue_number' => $queueNumber,
                'service_id' => $service->id,
                'counter_id' => $counter->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'passenger_count' => $request->passenger_count,
                'pickup_location' => $request->pickup_location ?? 'Pelabuhan Utama',
                'destination' => $request->destination,
                'distance_km' => $request->distance_km,
                'total_price' => $totalPrice,
                'telegram_chat_id' => $request->telegram_chat_id,
                'notes' => $request->notes,
                'is_priority' => $request->priority ?? false,
                'status' => 'WAITING',
                'created_by' => $user->id,
                'estimated_wait_time' => $this->calculateEstimatedWaitTime($counter->id)
            ]);
            
            // Save service data as JSON for backup
            $queueTicket->service_data = json_encode([
                'name' => $service->name,
                'type' => $service->type,
                'sub_type' => $service->sub_type,
                'base_price' => $service->base_price,
                'price_per_km' => $service->price_per_km,
                'capacity' => $service->capacity
            ]);
            $queueTicket->save();
            
            DB::commit();
            
            // Send Telegram notification if chat_id provided
            if ($request->telegram_chat_id) {
                $this->sendTelegramNotification($queueTicket);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dibuat',
                'ticket' => [
                    'id' => $queueTicket->id,
                    'ticket_number' => $queueTicket->ticket_number,
                    'queue_number' => $queueTicket->queue_number,
                    'customer_name' => $queueTicket->customer_name,
                    'service_name' => $service->name
                ],
                'print_url' => route('queue.print.ticket', $queueTicket->id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat antrian: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate queue number based on service and counter
     */
    private function generateQueueNumber($service, $counter)
    {
        // Get last queue number for this service today
        $lastQueue = QueueTicket::where('service_id', $service->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastQueue ? intval(substr($lastQueue->queue_number, -3)) + 1 : 1;
        
        // Determine prefix based on service type
        $prefix = match($service->type) {
            'PICKUP_TRANSPORTASI' => 'P',
            'TRANSUP' => 'T',
            'SEWA_MOTOR' => 'M',
            default => 'Q'
        };
        
        // Add counter code if needed
        $counterCode = $counter->code ? substr($counter->code, -1) : '1';
        
        return $prefix . $counterCode . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calculate estimated wait time
     */
    private function calculateEstimatedWaitTime($counterId)
    {
        $waitingCount = QueueTicket::where('counter_id', $counterId)
            ->where('status', 'WAITING')
            ->count();
        
        // Estimated 5 minutes per waiting ticket
        return ($waitingCount + 1) * 5;
    }
    
    /**
     * Send Telegram notification
     */
    private function sendTelegramNotification($queueTicket)
    {
        // Implement Telegram notification logic here
        // This will be integrated with your Telegram bot
        
        // For now, just log it
        \Log::info('Telegram notification would be sent for ticket: ' . $queueTicket->ticket_number, [
            'chat_id' => $queueTicket->telegram_chat_id,
            'queue_number' => $queueTicket->queue_number,
            'customer_name' => $queueTicket->customer_name
        ]);
    }
    
    /**
     * Display list of tickets
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'LOKET_STAFF') {
            return redirect()->route('dashboard');
        }
        
        $counter = $user->counter;
        
        if (!$counter) {
            return redirect()->route('loket.index')
                ->with('error', 'Anda tidak ditugaskan ke loket manapun.');
        }
        
        $tickets = QueueTicket::with('service')
            ->where('counter_id', $counter->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('queue.tickets', compact('tickets', 'counter'));
    }
    
    /**
     * Display ticket details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'LOKET_STAFF') {
            return redirect()->route('dashboard');
        }
        
        $ticket = QueueTicket::with(['service', 'counter'])
            ->findOrFail($id);
        
        // Check if ticket belongs to user's counter
        if ($user->counter_id != $ticket->counter_id && $user->role !== 'ADMIN') {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        return view('queue.show', compact('ticket'));
    }
    
    /**
     * Print ticket
     */
    public function printTicket($id)
    {
        $ticket = QueueTicket::with(['service', 'counter'])
            ->findOrFail($id);
        
        return view('queue.print.ticket', compact('ticket'));
    }
    
    /**
     * API: Get services by type
     */
    public function getServicesByType(Request $request)
    {
        $type = $request->get('type');
        
        $services = Service::where('type', $type)
            ->where('is_active', true)
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'type' => $service->type,
                    'sub_type' => $service->sub_type,
                    'base_price' => $service->base_price,
                    'price_per_km' => $service->price_per_km,
                    'capacity' => $service->capacity,
                    'estimated_time_minutes' => $service->estimated_time_minutes,
                    'icon' => $service->icon
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
    
    /**
     * API: Get queue estimation
     */
    public function getQueueEstimation(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id'
        ]);
        
        $service = Service::find($request->service_id);
        $counter = Auth::user()->counter;
        
        if (!$counter) {
            return response()->json([
                'success' => false,
                'message' => 'Counter not found'
            ], 400);
        }
        
        // Count waiting tickets for this service
        $waitingCount = QueueTicket::where('service_id', $service->id)
            ->where('counter_id', $counter->id)
            ->where('status', 'WAITING')
            ->count();
        
        // Generate next queue number
        $nextQueueNumber = $this->generateNextQueueNumber($service, $counter);
        
        // Calculate estimated wait time (5 minutes per waiting ticket)
        $estimatedWaitMinutes = ($waitingCount + 1) * 5;
        
        return response()->json([
            'success' => true,
            'data' => [
                'queue_number' => $nextQueueNumber,
                'estimated_wait_minutes' => $estimatedWaitMinutes,
                'current_waiting' => $waitingCount
            ]
        ]);
    }
    
    private function generateNextQueueNumber($service, $counter)
    {
        $lastQueue = QueueTicket::where('service_id', $service->id)
            ->where('counter_id', $counter->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastQueue ? intval(substr($lastQueue->queue_number, -3)) + 1 : 1;
        
        $prefix = match($service->type) {
            'PICKUP_TRANSPORTASI' => 'P',
            'TRANSUP' => 'T',
            'SEWA_MOTOR' => 'M',
            default => 'Q'
        };
        
        $counterCode = $counter->code ? substr($counter->code, -1) : '1';
        
        return $prefix . $counterCode . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
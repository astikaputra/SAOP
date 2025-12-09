<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\Counter;
use App\Models\Driver;
use App\Services\QueueService;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class QueueTicketController extends Controller
{
    protected $queueService;
    protected $telegramService;

    public function __construct(QueueService $queueService, TelegramNotificationService $telegramService)
    {
        $this->queueService = $queueService;
        $this->telegramService = $telegramService;
        
        $this->middleware('auth');
        $this->middleware('role:SUPER_ADMIN,ADMIN,LOKET_STAFF')->except(['create', 'store', 'print', 'publicCreate', 'publicStore']);
    }

    /**
     * Display a listing of queue tickets
     */
    public function index(Request $request)
    {
        $query = QueueTicket::with(['service', 'counter', 'calledBy', 'servedBy', 'driver']);
        
        // Filters
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->filled('counter_id')) {
            $query->where('counter_id', $request->counter_id);
        }
        
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', today());
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
        
        $tickets = $query->paginate(20)->withQueryString();
        
        // Get filter options
        $services = Service::where('is_active', true)->get();
        $counters = Counter::where('is_active', true)->get();
        
        $statuses = [
            'WAITING' => 'Menunggu',
            'CALLED' => 'Dipanggil',
            'SERVING' => 'Sedang Dilayani',
            'COMPLETED' => 'Selesai',
            'CANCELLED' => 'Dibatalkan',
            'NO_SHOW' => 'Tidak Datang',
        ];
        
        return view('queue.index', compact('tickets', 'services', 'counters', 'statuses'));
    }

    /**
     * Show the form for creating a new queue ticket (Admin/Loket)
     */
    public function create()
    {
        $services = Service::where('is_active', true)->get();
        return view('queue.create', compact('services'));
    }

    /**
     * Show the form for creating a new queue ticket (Public)
     */
    public function publicCreate()
    {
        $services = Service::where('is_active', true)->get();
        return view('queue.public-create', compact('services'));
    }

    /**
     * Store a newly created queue ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'passenger_count' => 'required|integer|min:1|max:10',
            'pickup_location' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'telegram_chat_id' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $ticket = $this->queueService->createTicket($validated);
            
            // Send Telegram notification if chat_id provided
            if (!empty($validated['telegram_chat_id'])) {
                $this->telegramService->sendQueueNotification($ticket);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'ticket' => $ticket,
                    'print_url' => route('queue.print', $ticket->id),
                    'message' => 'Tiket berhasil dibuat: ' . $ticket->ticket_number
                ]);
            }
            
            return redirect()->route('queue.show', $ticket)
                ->with('success', 'Tiket berhasil dibuat: ' . $ticket->ticket_number);
            
        } catch (\Exception $e) {
            \Log::error('Error creating ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat tiket: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Gagal membuat tiket: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified queue ticket
     */
    public function show(QueueTicket $ticket)
    {
        $ticket->load(['service', 'counter', 'calledBy', 'servedBy', 'driver.user']);
        return view('queue.show', compact('ticket'));
    }

    /**
     * Call a ticket to a counter
     */
    public function call(Request $request, QueueTicket $ticket)
    {
        $request->validate([
            'counter_id' => 'required|exists:counters,id'
        ]);

        try {
            $counter = Counter::findOrFail($request->counter_id);
            
            // Check if counter is available
            if (!$counter->is_active || $counter->status === 'MAINTENANCE') {
                throw new \Exception('Loket tidak tersedia');
            }
            
            $ticket = $this->queueService->callTicket($ticket, $counter, auth()->user());
            
            // Send Telegram notification
            if ($ticket->telegram_chat_id) {
                $this->telegramService->sendCallNotification($ticket, $counter);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'ticket' => $ticket,
                    'counter' => $counter,
                    'message' => 'Antrian berhasil dipanggil ke ' . $counter->name
                ]);
            }
            
            return back()->with('success', 'Antrian berhasil dipanggil');
            
        } catch (\Exception $e) {
            \Log::error('Error calling ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memanggil antrian: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal memanggil antrian: ' . $e->getMessage());
        }
    }

    /**
     * Complete a ticket
     */
    public function complete(Request $request, QueueTicket $ticket)
    {
        $request->validate([
            'driver_id' => 'nullable|exists:drivers,id',
            'final_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $driver = $request->driver_id ? Driver::find($request->driver_id) : null;
            
            $ticket = $this->queueService->completeTicket(
                $ticket, 
                auth()->user(), 
                $driver, 
                $request->all()
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'ticket' => $ticket,
                    'message' => 'Antrian berhasil diselesaikan'
                ]);
            }
            
            return back()->with('success', 'Antrian berhasil diselesaikan');
            
        } catch (\Exception $e) {
            \Log::error('Error completing ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyelesaikan antrian: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menyelesaikan antrian: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a ticket
     */
    public function cancel(Request $request, QueueTicket $ticket)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $ticket = $this->queueService->cancelTicket($ticket, auth()->user(), $request->reason);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'ticket' => $ticket,
                    'message' => 'Antrian berhasil dibatalkan'
                ]);
            }
            
            return back()->with('success', 'Antrian berhasil dibatalkan');
            
        } catch (\Exception $e) {
            \Log::error('Error cancelling ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membatalkan antrian: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal membatalkan antrian: ' . $e->getMessage());
        }
    }

    /**
     * Skip a ticket (return to queue)
     */
    public function skip(Request $request, QueueTicket $ticket)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $ticket = $this->queueService->skipTicket($ticket, auth()->user(), $request->reason);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'ticket' => $ticket,
                    'message' => 'Antrian berhasil dilewati'
                ]);
            }
            
            return back()->with('success', 'Antrian berhasil dilewati');
            
        } catch (\Exception $e) {
            \Log::error('Error skipping ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melewati antrian: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal melewati antrian: ' . $e->getMessage());
        }
    }

    /**
     * Print ticket
     */
    public function print(QueueTicket $ticket)
    {
        $ticket->load('service');
        
        $data = [
            'ticket' => $ticket,
            'date' => now()->format('d/m/Y H:i:s'),
            'port_name' => \App\Models\Setting::getValue('port_name', 'Pelabuhan Utama'),
            'app_name' => \App\Models\Setting::getValue('app_name', 'Sistem Antrian Ojek Pelabuhan'),
        ];
        
        // Return PDF for printing
        $pdf = Pdf::loadView('queue.print.ticket', $data);
        return $pdf->stream($ticket->ticket_number . '.pdf');
    }

    /**
     * Get waiting tickets for display board
     */
    public function getWaitingTickets(Request $request)
    {
        $serviceType = $request->get('service_type');
        
        $query = QueueTicket::with('service')
            ->where('status', 'WAITING');
        
        if ($serviceType) {
            $query->whereHas('service', function($q) use ($serviceType) {
                $q->where('type', $serviceType);
            });
        }
        
        $tickets = $query->orderBy('queue_number')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get current tickets (called/serving)
     */
    public function getCurrentTickets(Request $request)
    {
        $tickets = QueueTicket::with(['service', 'counter'])
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->orderBy('called_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function getStats(Request $request)
    {
        $stats = $this->queueService->getDashboardStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\QueueTicket;
use App\Models\Driver;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoketController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display loket dashboard
     */
    public function index()
    {
        // Manual middleware check
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return redirect()->route('dashboard')
                ->with('error', 'Hanya staff loket yang dapat mengakses halaman ini.');
        }
        
        if (!$user->counter_id) {
            // Assign user ke counter
            $counter = $this->assignUserToCounter($user);
        } else {
            $counter = Counter::find($user->counter_id);
        }
        
        if (!$counter) {
            return redirect()->route('dashboard')
                ->with('error', 'Counter tidak ditemukan. Hubungi administrator.');
        }
        
        // Update user_id di counter jika masih null
        if (!$counter->user_id) {
            $counter->user_id = $user->id;
            $counter->save();
        }
        
        // Get waiting tickets for this counter's services
        $serviceTypes = $counter->getServiceTypesSafe();
        
        $waitingTickets = QueueTicket::with('service')
            ->where('status', 'WAITING')
            ->whereHas('service', function($query) use ($serviceTypes) {
                $query->whereIn('type', $serviceTypes);
            })
            ->orderBy('queue_number')
            ->get();
        
        // Get current ticket (being served)
        $currentTicket = null;
        if ($counter->current_ticket_id) {
            $currentTicket = QueueTicket::with('service')
                ->where('id', $counter->current_ticket_id)
                ->first();
        }
        
        // Jika tidak ada di counter, cari yang sedang dipanggil
        if (!$currentTicket) {
            $currentTicket = QueueTicket::with('service')
                ->where('counter_id', $counter->id)
                ->whereIn('status', ['CALLED', 'SERVING'])
                ->orderBy('called_at', 'desc')
                ->first();
        }
        
        // Recently called tickets (last 10)
        $recentCalled = QueueTicket::with('service')
            ->where('counter_id', $counter->id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->orderBy('called_at', 'desc')
            ->limit(10)
            ->get();
        
        // Statistics for today
        $today = now()->format('Y-m-d');
        $stats = [
            'called_today' => QueueTicket::where('counter_id', $counter->id)
                ->whereDate('called_at', $today)
                ->count(),
            'completed_today' => QueueTicket::where('counter_id', $counter->id)
                ->where('status', 'COMPLETED')
                ->whereDate('completed_at', $today)
                ->count(),
            'avg_wait_time' => $this->calculateAverageWaitTime($counter->id),
        ];
        
        // Available drivers
        $availableDrivers = Driver::with('user')
            ->where('status', 'AVAILABLE')
            ->get();
        
        return view('loket.index', compact(
            'counter',
            'waitingTickets',
            'currentTicket',
            'recentCalled',
            'stats',
            'availableDrivers'
        ));
    }
    
    /**
     * Assign user to appropriate counter
     */
    private function assignUserToCounter($user)
    {
        // Cari counter berdasarkan nama user atau ambil yang sesuai
        $counter = null;
        
        // Coba match berdasarkan nama
        if (str_contains(strtoupper($user->name), 'PICKUP')) {
            $counter = Counter::where('code', 'LOKET-1')->first();
        } elseif (str_contains(strtoupper($user->name), 'TRANSUP')) {
            $counter = Counter::where('code', 'LOKET-2')->first();
        } elseif (str_contains(strtoupper($user->name), 'MOTOR')) {
            $counter = Counter::where('code', 'LOKET-3')->first();
        }
        
        // Jika tidak ditemukan, ambil counter yang belum ada user
        if (!$counter) {
            $counter = Counter::whereNull('user_id')->first();
        }
        
        // Jika masih tidak ada, ambil counter pertama
        if (!$counter) {
            $counter = Counter::where('status', 'ACTIVE')->first();
        }
        
        if ($counter) {
            // Update user dengan counter_id
            $user->counter_id = $counter->id;
            $user->save();
            
            // Update counter dengan user_id
            $counter->user_id = $user->id;
            $counter->save();
        }
        
        return $counter;
    }

    /**
     * Update loket staff status
     */
    public function updateStatus(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $request->validate([
            'counter_status' => 'required|in:AVAILABLE,BUSY,BREAK,OFFLINE'
        ]);
        
        $user->update([
            'counter_status' => $request->counter_status,
            'last_active_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
            'status' => $user->counter_status
        ]);
    }

    /**
     * Call next ticket
     */
    public function callNext(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        if (!$counter) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan ke loket'
            ], 400);
        }
        
        // Check if counter is currently serving a ticket
        if ($counter->current_ticket_id) {
            return response()->json([
                'success' => false,
                'message' => 'Loket masih melayani antrian'
            ], 400);
        }
        
        // Get next waiting ticket
        $nextTicket = $this->queueService->getNextTicketForCounter($counter);
        
        if (!$nextTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada antrian menunggu'
            ], 404);
        }
        
        try {
            $this->queueService->callTicket($nextTicket, $counter, $user);
            
            // Update user status
            $user->update([
                'counter_status' => 'BUSY',
                'last_active_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'ticket' => $nextTicket->fresh(),
                'message' => 'Antrian berhasil dipanggil: ' . $nextTicket->ticket_number
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete current ticket
     */
    public function completeCurrent(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        if (!$counter || !$counter->current_ticket_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada antrian yang sedang dilayani'
            ], 400);
        }
        
        $ticket = QueueTicket::find($counter->current_ticket_id);
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }
        
        try {
            $this->queueService->completeTicket($ticket, $user, null, $request->all());
            
            // Update user status
            $user->update([
                'counter_status' => 'AVAILABLE',
                'last_active_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil diselesaikan'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Skip current ticket
     */
    public function skipCurrent(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        if (!$counter || !$counter->current_ticket_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada antrian yang sedang dilayani'
            ], 400);
        }
        
        $ticket = QueueTicket::find($counter->current_ticket_id);
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }
        
        try {
            $this->queueService->skipTicket($ticket, $user, $request->reason ?? '');
            
            // Update user status
            $user->update([
                'counter_status' => 'AVAILABLE',
                'last_active_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dilewati'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melewati antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get queue data for display (AJAX)
     */
    public function getQueueData(Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        if (!$counter) {
            return response()->json([
                'success' => false,
                'message' => 'Counter not found'
            ], 400);
        }
        
        $serviceTypes = $counter->getServiceTypesSafe();
        
        $waitingTickets = QueueTicket::with('service')
            ->where('status', 'WAITING')
            ->whereHas('service', function($query) use ($serviceTypes) {
                $query->whereIn('type', $serviceTypes);
            })
            ->orderBy('queue_number')
            ->get();
        
        // Get current ticket
        $currentTicket = null;
        if ($counter->current_ticket_id) {
            $currentTicket = QueueTicket::with('service')
                ->where('id', $counter->current_ticket_id)
                ->first();
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'waiting_count' => $waitingTickets->count(),
                'current_ticket' => $currentTicket,
                'waiting_tickets' => $waitingTickets->take(5)
            ]
        ]);
    }

    /**
     * Call specific ticket
     */
    public function callSpecific($id)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        if (!$counter) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan ke loket'
            ], 400);
        }
        
        if ($counter->current_ticket_id) {
            return response()->json([
                'success' => false,
                'message' => 'Loket masih melayani antrian'
            ], 400);
        }
        
        $ticket = QueueTicket::find($id);
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }
        
        // Check if ticket is for this counter's service
        $serviceTypes = $counter->getServiceTypesSafe();
        if (!in_array($ticket->service->type, $serviceTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket bukan untuk layanan loket ini'
            ], 400);
        }
        
        try {
            $this->queueService->callTicket($ticket, $counter, $user);
            
            $user->update([
                'counter_status' => 'BUSY',
                'last_active_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'ticket' => $ticket->fresh(),
                'message' => 'Antrian berhasil dipanggil: ' . $ticket->ticket_number
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Skip specific ticket
     */
    public function skipTicket($id, Request $request)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $counter = $user->counter;
        
        $ticket = QueueTicket::find($id);
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }
        
        // Only allow skipping waiting tickets
        if ($ticket->status != 'WAITING') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya antrian menunggu yang bisa dilewati'
            ], 400);
        }
        
        try {
            $this->queueService->skipTicket($ticket, $user, $request->reason ?? '');
            
            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dilewati'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melewati antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recall current ticket
     */
    public function recallTicket($id)
    {
        $user = Auth::user();
        
        // Role check
        if ($user->role !== 'LOKET_STAFF') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $ticket = QueueTicket::find($id);
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ], 404);
        }
        
        if ($ticket->status != 'CALLED' && $ticket->status != 'SERVING') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya antrian yang sedang dipanggil yang bisa dipanggil ulang'
            ], 400);
        }
        
        // Send recall notification
        // To be implemented with Telegram
        
        return response()->json([
            'success' => true,
            'message' => 'Antrian dipanggil ulang'
        ]);
    }

    /**
     * Calculate average wait time for counter
     */
    private function calculateAverageWaitTime($counterId)
    {
        $today = now()->format('Y-m-d');
        
        $avgTime = QueueTicket::where('counter_id', $counterId)
            ->where('status', 'COMPLETED')
            ->whereDate('completed_at', $today)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, called_at, completed_at)) as avg_time')
            ->first();
        
        return $avgTime->avg_time ? round($avgTime->avg_time / 60, 1) : 0;
    }
}
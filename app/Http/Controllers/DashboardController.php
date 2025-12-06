<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Counter;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        switch ($user->role) {
            case 'SUPER_ADMIN':
            case 'ADMIN':
            case 'MANAGER':
                return $this->adminDashboard();
            case 'LOKET_STAFF':
                return $this->loketDashboard();
            case 'DRIVER':
                return $this->driverDashboard();
            default:
                return redirect('/');
        }
    }

    private function adminDashboard()
    {
        $stats = [
            'total_tickets_today' => QueueTicket::whereDate('created_at', today())->count(),
            'waiting_tickets' => QueueTicket::where('status', 'WAITING')->count(),
            'called_tickets' => QueueTicket::where('status', 'CALLED')->count(),
            'active_counters' => Counter::where('is_active', true)->count(),
            'available_drivers' => Driver::where('status', 'AVAILABLE')->count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    // private function loketDashboard()
    // {
    //     try {
    //         $user = auth()->user();
            
    //         // Debug: Cek user
    //         // \Log::info('Loket Dashboard - User:', ['user' => $user->toArray()]);
            
    //         $counter = $user->counter;
            
    //         if (!$counter) {
    //             \Log::error('Counter not found for user', ['user_id' => $user->id, 'counter_id' => $user->counter_id]);
    //             return redirect()->route('dashboard')
    //                 ->with('error', 'Anda belum ditugaskan ke loket manapun. Silakan hubungi administrator.');
    //         }

    //         // Debug: Cek counter
    //         // \Log::info('Counter found:', ['counter' => $counter->toArray()]);
            
    //         // Dapatkan service types dengan aman
    //         $serviceTypes = $counter->getServiceTypesSafe();
            
    //         // Debug: Cek service types
    //         // \Log::info('Service types:', ['types' => $serviceTypes]);
            
    //         if (empty($serviceTypes)) {
    //             \Log::warning('Service types empty for counter', ['counter_id' => $counter->id]);
    //             // Return dengan empty waiting tickets
    //             $waitingTickets = collect([]);
    //         } else {
    //             // Get waiting tickets hanya untuk service types yang ada
    //             $waitingTickets = QueueTicket::with('service')
    //                 ->where('status', 'WAITING')
    //                 ->whereHas('service', function($query) use ($serviceTypes) {
    //                     $query->whereIn('type', $serviceTypes);
    //                 })
    //                 ->orderBy('queue_number')
    //                 ->get();
    //         }
            
    //         // Debug: Cek waiting tickets
    //         // \Log::info('Waiting tickets count:', ['count' => $waitingTickets->count()]);

    //         return view('dashboard.loket', compact('counter', 'waitingTickets'));
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error in loketDashboard:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
            
    //         return redirect()->route('dashboard')
    //             ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    //     }
    // }

    private function loketDashboard()
    {
        $user = auth()->user();
        $counter = $user->counter;
        
        if (!$counter) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke loket manapun.');
        }

        // Handle service_types dengan berbagai kemungkinan
        $serviceTypes = [];
        
        if (is_array($counter->service_types)) {
            $serviceTypes = $counter->service_types;
        } elseif (is_string($counter->service_types)) {
            $decoded = json_decode($counter->service_types, true);
            $serviceTypes = is_array($decoded) ? $decoded : [];
        }
        
        // Jika masih kosong, beri default berdasarkan loket
        if (empty($serviceTypes)) {
            $serviceTypes = match($counter->code) {
                'LOKET-1' => ['PICKUP_TRANSPORTASI'],
                'LOKET-2' => ['TRANSUP'],
                'LOKET-3' => ['SEWA_MOTOR'],
                'LOKET-VIP' => ['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR'],
                default => []
            };
        }

        // Get waiting tickets
        $waitingTickets = QueueTicket::with('service')
            ->where('status', 'WAITING')
            ->whereHas('service', function($query) use ($serviceTypes) {
                $query->whereIn('type', $serviceTypes);
            })
            ->orderBy('queue_number')
            ->get();

        return view('dashboard.loket', compact('counter', 'waitingTickets'));
    }

    private function driverDashboard()
    {
        try {
            $user = auth()->user();
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return redirect()->back()->with('error', 'Profil driver tidak ditemukan.');
            }

            $currentTrip = QueueTicket::where('driver_id', $user->id)
                ->whereIn('status', ['CALLED', 'SERVING'])
                ->first();

            $todayTrips = QueueTicket::where('driver_id', $user->id)
                ->where('status', 'COMPLETED')
                ->whereDate('completed_at', today())
                ->get();

            return view('dashboard.driver', compact('driver', 'currentTrip', 'todayTrips'));
            
        } catch (\Exception $e) {
            \Log::error('Error in driverDashboard:', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
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

    private function loketDashboard()
    {
        $user = auth()->user();
        $counter = $user->counter;
        
        if (!$counter) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan ke loket manapun.');
        }

        $waitingTickets = QueueTicket::where('status', 'WAITING')
            ->whereHas('service', function($query) use ($counter) {
                $serviceTypes = json_decode($counter->service_types, true) ?? [];
                $query->whereIn('type', $serviceTypes);
            })
            ->orderBy('created_at')
            ->get();

        return view('dashboard.loket', compact('counter', 'waitingTickets'));
    }

    private function driverDashboard()
    {
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
    }
}
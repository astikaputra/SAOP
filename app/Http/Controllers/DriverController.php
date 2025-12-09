<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DriverController extends Controller
{
    /**
     * Display driver dashboard
     */
    public function dashboard()
    {
        $driver = Auth::user();
        $today = Carbon::today();
        
        // Get today's statistics
        $todayTasks = QueueTicket::where('driver_id', $driver->id)
            ->whereDate('created_at', $today)
            ->count();
            
        $todayCompleted = QueueTicket::where('driver_id', $driver->id)
            ->whereDate('served_at', $today)
            ->where('status', 'completed')
            ->count();
            
        $todayPending = QueueTicket::where('driver_id', $driver->id)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['waiting', 'processing'])
            ->count();
        
        // Get current active task
        $currentTask = QueueTicket::where('driver_id', $driver->id)
            ->whereIn('status', ['processing'])
            ->with('service', 'counter')
            ->first();
        
        // Get pending tasks
        $pendingTasks = QueueTicket::where('driver_id', $driver->id)
            ->where('status', 'waiting')
            ->with('service', 'counter')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();
        
        return view('driver.dashboard', compact(
            'driver', 
            'todayTasks', 
            'todayCompleted', 
            'todayPending',
            'currentTask',
            'pendingTasks'
        ));
    }
    
    /**
     * Display driver tasks
     */
    public function tasks()
    {
        $driver = Auth::user();
        
        $tasks = QueueTicket::where('driver_id', $driver->id)
            ->whereIn('status', ['waiting', 'processing'])
            ->with('service', 'counter')
            ->orderBy('created_at', 'asc')
            ->paginate(10);
        
        return view('driver.tasks', compact('driver', 'tasks'));
    }
    
    /**
     * Show specific task
     */
    public function showTask(QueueTicket $queueTicket)
    {
        $driver = Auth::user();
        
        // Check if driver is assigned to this task
        if ($queueTicket->driver_id !== $driver->id) {
            abort(403, 'Unauthorized access to this task.');
        }
        
        $queueTicket->load('service', 'counter');
        
        return view('driver.task-show', compact('driver', 'queueTicket'));
    }
    
    /**
     * Update task status
     */
    public function updateStatus(Request $request, QueueTicket $queueTicket)
    {
        $request->validate([
            'status' => 'required|in:picked_up,on_the_way,arrived,completed'
        ]);
        
        $driver = Auth::user();
        
        // Check if driver is assigned to this task
        if ($queueTicket->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this task.'
            ], 403);
        }
        
        try {
            $status = $request->status;
            $queueTicket->status = $status;
            
            // Update timestamps based on status
            switch ($status) {
                case 'picked_up':
                    $queueTicket->picked_up_at = now();
                    break;
                case 'on_the_way':
                    $queueTicket->on_the_way_at = now();
                    break;
                case 'arrived':
                    $queueTicket->arrived_at = now();
                    break;
                case 'completed':
                    $queueTicket->served_at = now();
                    break;
            }
            
            $queueTicket->save();
            
            // Send notification if needed
            if ($queueTicket->telegram_chat_id) {
                $this->sendStatusUpdateNotification($queueTicket, $status);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display driver history
     */
    public function history()
    {
        $driver = Auth::user();
        
        $history = QueueTicket::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->with('service', 'counter')
            ->orderBy('served_at', 'desc')
            ->paginate(10);
        
        return view('driver.history', compact('driver', 'history'));
    }
    
    /**
     * Display driver profile
     */
    public function profile()
    {
        $driver = Auth::user();
        return view('driver.profile', compact('driver'));
    }
    
    /**
     * Update driver profile
     */
    public function updateProfile(Request $request)
    {
        $driver = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $driver->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048'
        ]);
        
        try {
            // Update user data
            $driver->name = $validated['name'];
            $driver->email = $validated['email'];
            $driver->phone = $validated['phone'];
            
            // Update driver-specific data if exists
            if (isset($validated['vehicle_type'])) {
                $driver->vehicle_type = $validated['vehicle_type'];
            }
            
            if (isset($validated['vehicle_number'])) {
                $driver->vehicle_number = $validated['vehicle_number'];
            }
            
            if (isset($validated['address'])) {
                $driver->address = $validated['address'];
            }
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('driver-photos', 'public');
                $driver->photo = $photoPath;
            }
            
            $driver->save();
            
            return redirect()->route('driver.profile')
                ->with('success', 'Profile updated successfully.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Send status update notification via Telegram
     */
    private function sendStatusUpdateNotification(QueueTicket $queueTicket, $status)
    {
        $statusMessages = [
            'picked_up' => '🚗 *Driver telah menjemput penumpang*',
            'on_the_way' => '🛣️ *Dalam perjalanan menuju tujuan*',
            'arrived' => '📍 *Telah tiba di lokasi tujuan*',
            'completed' => '✅ *Perjalanan telah selesai*'
        ];
        
        $message = $statusMessages[$status] ?? '📋 *Update Status Perjalanan*' . "\n\n";
        $message .= "📋 *No. Tiket:* {$queueTicket->ticket_number}\n";
        $message .= "👤 *Nama:* {$queueTicket->customer_name}\n";
        $message .= "🚗 *Layanan:* {$queueTicket->service->name}\n";
        $message .= "📍 *Tujuan:* {$queueTicket->destination}\n";
        $message .= "⏳ *Status:* " . $this->getStatusText($status) . "\n";
        $message .= "\nTerima kasih telah menggunakan layanan kami!";
        
        // Use your Telegram service
        if (class_exists('App\Services\TelegramService')) {
            $telegramService = new \App\Services\TelegramService();
            $telegramService->sendMessage($queueTicket->telegram_chat_id, $message);
        }
    }
    
    /**
     * Get status text in Indonesian
     */
    private function getStatusText($status)
    {
        $statusTexts = [
            'picked_up' => 'Sedang dijemput',
            'on_the_way' => 'Dalam perjalanan',
            'arrived' => 'Telah tiba',
            'completed' => 'Selesai'
        ];
        
        return $statusTexts[$status] ?? $status;
    }
}
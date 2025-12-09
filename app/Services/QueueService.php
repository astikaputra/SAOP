<?php

namespace App\Services;

use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\Counter;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueService
{
    /**
     * Create a new queue ticket
     */
    public function createTicket(array $data)
    {
        return DB::transaction(function () use ($data) {
            $service = Service::findOrFail($data['service_id']);
            
            // Generate ticket number
            $ticketNumber = $this->generateTicketNumber($service->type);
            
            // Get queue number for today
            $queueNumber = $this->getNextQueueNumber($service->id);
            
            // Calculate estimated price
            $estimatedPrice = $this->calculateEstimatedPrice($service, $data);
            
            // Create ticket
            $ticket = QueueTicket::create([
                'ticket_number' => $ticketNumber,
                'service_id' => $service->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'passenger_count' => $data['passenger_count'] ?? 1,
                'pickup_location' => $data['pickup_location'] ?? 'Pelabuhan Utama',
                'destination' => $data['destination'] ?? null,
                'distance_km' => $data['distance_km'] ?? null,
                'queue_number' => $queueNumber,
                'status' => 'WAITING',
                'estimated_price' => $estimatedPrice,
                'telegram_chat_id' => $data['telegram_chat_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Log audit
            $this->logAudit('CREATE', $ticket, null, $ticket->toArray());
            
            return $ticket;
        });
    }
    
    /**
     * Call a ticket to a counter
     */
    public function callTicket(QueueTicket $ticket, Counter $counter, User $user)
    {
        return DB::transaction(function () use ($ticket, $counter, $user) {
            $oldData = $ticket->toArray();
            
            // Update ticket
            $ticket->update([
                'status' => 'CALLED',
                'counter_id' => $counter->id,
                'called_by' => $user->id,
                'called_at' => now(),
            ]);
            
            // Update counter
            $counter->update([
                'current_ticket_id' => $ticket->id,
            ]);
            
            // Log audit
            $this->logAudit('CALL', $ticket, $oldData, $ticket->toArray(), $user);
            
            return $ticket->fresh();
        });
    }
    
    /**
     * Complete a ticket
     */
    public function completeTicket(QueueTicket $ticket, User $user, ?Driver $driver = null, array $data = [])
    {
        return DB::transaction(function () use ($ticket, $user, $driver, $data) {
            $oldData = $ticket->toArray();
            
            // Update ticket
            $ticket->update([
                'status' => 'COMPLETED',
                'served_by' => $user->id,
                'driver_id' => $driver ? $driver->user_id : null,
                'final_price' => $data['final_price'] ?? $ticket->estimated_price,
                'completed_at' => now(),
                'notes' => $this->appendNotes($ticket->notes, $data['notes'] ?? null),
            ]);
            
            // Update counter
            if ($ticket->counter_id) {
                Counter::where('id', $ticket->counter_id)
                    ->update(['current_ticket_id' => null]);
            }
            
            // Update driver statistics
            if ($driver) {
                $driver->increment('total_trips');
                $driver->increment('total_earnings', $ticket->final_price ?? $ticket->estimated_price);
                
                // Update driver status
                $driver->update(['status' => 'AVAILABLE']);
            }
            
            // Log audit
            $this->logAudit('COMPLETE', $ticket, $oldData, $ticket->toArray(), $user);
            
            return $ticket->fresh();
        });
    }
    
    /**
     * Cancel a ticket
     */
    public function cancelTicket(QueueTicket $ticket, User $user, string $reason)
    {
        return DB::transaction(function () use ($ticket, $user, $reason) {
            $oldData = $ticket->toArray();
            
            // Update ticket
            $ticket->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'notes' => $this->appendNotes($ticket->notes, "Dibatalkan: " . $reason),
            ]);
            
            // Update counter if ticket was called
            if ($ticket->counter_id) {
                Counter::where('id', $ticket->counter_id)
                    ->where('current_ticket_id', $ticket->id)
                    ->update(['current_ticket_id' => null]);
            }
            
            // Log audit
            $this->logAudit('CANCEL', $ticket, $oldData, $ticket->toArray(), $user);
            
            return $ticket->fresh();
        });
    }
    
    /**
     * Skip current ticket (return to queue)
     */
    public function skipTicket(QueueTicket $ticket, User $user, string $reason = '')
    {
        return DB::transaction(function () use ($ticket, $user, $reason) {
            $oldData = $ticket->toArray();
            
            // Update ticket
            $ticket->update([
                'status' => 'WAITING',
                'counter_id' => null,
                'called_at' => null,
                'notes' => $this->appendNotes($ticket->notes, "Dilewati: " . $reason),
            ]);
            
            // Update counter
            if ($ticket->counter_id) {
                Counter::where('id', $ticket->counter_id)
                    ->update(['current_ticket_id' => null]);
            }
            
            // Log audit
            $this->logAudit('SKIP', $ticket, $oldData, $ticket->toArray(), $user);
            
            return $ticket->fresh();
        });
    }
    
    /**
     * Get next queue number for a service today
     */
    private function getNextQueueNumber(int $serviceId): int
    {
        $lastTicket = QueueTicket::where('service_id', $serviceId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('queue_number', 'desc')
            ->first();
        
        return ($lastTicket ? $lastTicket->queue_number : 0) + 1;
    }
    
    /**
     * Generate ticket number
     */
    private function generateTicketNumber(string $serviceType): string
    {
        $prefix = match($serviceType) {
            'PICKUP_TRANSPORTASI' => 'PICK',
            'TRANSUP' => 'TRAN',
            'SEWA_MOTOR' => 'SEWA',
            default => 'TICK'
        };
        
        $date = Carbon::now()->format('ymd');
        
        // Get last ticket number for today
        $lastTicket = QueueTicket::where('ticket_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -3);
            $number = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $number = '001';
        }
        
        return "{$prefix}-{$date}-{$number}";
    }
    
    /**
     * Calculate estimated price
     */
    private function calculateEstimatedPrice(Service $service, array $data): float
    {
        $price = $service->base_price;
        
        // Add distance-based price if applicable
        if ($service->price_per_km && isset($data['distance_km']) && $data['distance_km'] > 0) {
            $price += $service->price_per_km * $data['distance_km'];
        }
        
        return $price;
    }
    
    /**
     * Append notes
     */
    private function appendNotes(?string $existingNotes, ?string $newNotes): string
    {
        if (empty($newNotes)) {
            return $existingNotes ?? '';
        }
        
        if (empty($existingNotes)) {
            return $newNotes;
        }
        
        return $existingNotes . " | " . $newNotes;
    }
    
    /**
     * Log audit trail
     */
    private function logAudit(string $action, $model, $oldData, $newData, $user = null)
    {
        \App\Models\AuditLog::create([
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldData,
            'new_values' => $newData,
            'user_id' => $user ? $user->id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => "{$action} {$model->ticket_number}",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getDashboardStats()
    {
        $today = Carbon::today();
        
        return [
            'total_today' => QueueTicket::whereDate('created_at', $today)->count(),
            'waiting' => QueueTicket::where('status', 'WAITING')->count(),
            'called' => QueueTicket::where('status', 'CALLED')->count(),
            'serving' => QueueTicket::where('status', 'SERVING')->count(),
            'completed_today' => QueueTicket::where('status', 'COMPLETED')
                ->whereDate('completed_at', $today)
                ->count(),
            'cancelled_today' => QueueTicket::where('status', 'CANCELLED')
                ->whereDate('cancelled_at', $today)
                ->count(),
            'total_earnings_today' => QueueTicket::where('status', 'COMPLETED')
                ->whereDate('completed_at', $today)
                ->sum('final_price'),
        ];
    }
    
    /**
     * Get next ticket for a counter
     */
    public function getNextTicketForCounter(Counter $counter): ?QueueTicket
    {
        $serviceTypes = $counter->getServiceTypesSafe();
        
        if (empty($serviceTypes)) {
            return null;
        }
        
        return QueueTicket::with('service')
            ->where('status', 'WAITING')
            ->whereHas('service', function($query) use ($serviceTypes) {
                $query->whereIn('type', $serviceTypes);
            })
            ->orderBy('queue_number')
            ->first();
    }
    
    /**
     * Get estimated wait time for a service
     */
    public function getEstimatedWaitTime(int $serviceId): int
    {
        $waitingCount = QueueTicket::where('service_id', $serviceId)
            ->where('status', 'WAITING')
            ->count();
        
        // Assume 5 minutes per ticket
        return $waitingCount * 5;
    }
}
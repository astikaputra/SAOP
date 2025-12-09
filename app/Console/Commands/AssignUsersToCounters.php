<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Counter;

class AssignUsersToCounters extends Command
{
    protected $signature = 'counters:assign-users';
    protected $description = 'Assign users to counters based on their role';

    public function handle()
    {
        $this->info('Assigning users to counters...');
        
        // Reset semua counter user_id
        Counter::query()->update(['user_id' => null]);
        
        // Ambil semua user LOKET_STAFF
        $users = User::where('role', 'LOKET_STAFF')->get();
        
        foreach ($users as $user) {
            // Cari counter yang sesuai
            $counter = $this->findMatchingCounter($user);
            
            if ($counter) {
                $user->counter_id = $counter->id;
                $user->save();
                
                $counter->user_id = $user->id;
                $counter->save();
                
                $this->line("Assigned {$user->name} to {$counter->name}");
            } else {
                $this->warn("No counter found for {$user->name}");
            }
        }
        
        $this->info('Assignment completed!');
    }
    
    private function findMatchingCounter($user)
    {
        // Coba cari berdasarkan nama
        if (str_contains(strtoupper($user->name), 'PICKUP')) {
            return Counter::where('code', 'LOKET-1')->first();
        } elseif (str_contains(strtoupper($user->name), 'TRANSUP')) {
            return Counter::where('code', 'LOKET-2')->first();
        } elseif (str_contains(strtoupper($user->name), 'MOTOR')) {
            return Counter::where('code', 'LOKET-3')->first();
        }
        
        // Default: ambil counter yang belum ada user
        return Counter::whereNull('user_id')->first() ?? Counter::first();
    }
}
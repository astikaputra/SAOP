@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['title' => 'Dashboard']
        ];
    @endphp
    @include('layouts.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Tickets Today -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Antrian Hari Ini</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_tickets_today'] }}</p>
                    <div class="flex items-center mt-2">
                        @if($stats['yesterday_comparison'] > 0)
                            <span class="text-green-500 text-sm flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                {{ abs($stats['yesterday_comparison']) }}%
                            </span>
                        @else
                            <span class="text-red-500 text-sm flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i>
                                {{ abs($stats['yesterday_comparison']) }}%
                            </span>
                        @endif
                        <span class="text-gray-400 text-sm ml-2">dari kemarin</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Waiting Tickets -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Menunggu Dipanggil</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['waiting_tickets'] }}</p>
                    <p class="text-gray-400 text-sm mt-2">Estimasi tunggu: {{ ceil($stats['waiting_tickets'] * 5) }} menit</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Active Counters -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Loket Aktif</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['active_counters'] }}</p>
                    <p class="text-gray-400 text-sm mt-2">Dari {{ $counters->count() }} total loket</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-desktop text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Today's Earnings -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pendapatan Hari Ini</p>
                    <p class="text-3xl font-bold mt-2">Rp {{ number_format($stats['total_earnings_today'], 0, ',', '.') }}</p>
                    <p class="text-gray-400 text-sm mt-2">Rata-rata per antrian: Rp 75.000</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Queue Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold">Statistik Antrian 7 Hari Terakhir</h3>
                <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                    <option>7 Hari Terakhir</option>
                    <option>30 Hari Terakhir</option>
                    <option>Bulan Ini</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="queueChart"></canvas>
            </div>
        </div>
        
        <!-- Service Stats -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Status Layanan</h3>
            <div class="space-y-4">
                @foreach($serviceStats as $service)
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">{{ $service->name }}</p>
                            <p class="text-sm text-gray-500">{{ $service->type }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">{{ $service->waiting_count }}</p>
                            <p class="text-xs text-gray-500">menunggu</p>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php
                                $max = max(1, $serviceStats->max('waiting_count'));
                                $percentage = ($service->waiting_count / $max) * 100;
                            @endphp
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Recent Tickets and Counters -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tickets -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Antrian Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentTickets as $ticket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $ticket->customer_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ticket->service->name }}</div>
                                <div class="text-sm text-gray-500">{{ $ticket->service->type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'WAITING' => 'bg-yellow-100 text-yellow-800',
                                        'CALLED' => 'bg-blue-100 text-blue-800',
                                        'SERVING' => 'bg-purple-100 text-purple-800',
                                        'COMPLETED' => 'bg-green-100 text-green-800',
                                        'CANCELLED' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ticket->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 text-center">
                <a href="{{ route('queue.tickets') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua Antrian →
                </a>
            </div>
        </div>
        
        <!-- Counter Status -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Status Loket</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($counters as $counter)
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium">{{ $counter->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $counter->location }}</p>
                            <div class="mt-2">
                                @if($counter->currentTicket)
                                <p class="text-sm">
                                    <span class="text-gray-600">Sedang melayani:</span>
                                    <span class="font-medium ml-2">{{ $counter->currentTicket->ticket_number }}</span>
                                </p>
                                @else
                                <p class="text-sm text-gray-500">Tidak ada antrian</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @php
                                $counterStatusColors = [
                                    'ACTIVE' => 'bg-green-100 text-green-800',
                                    'INACTIVE' => 'bg-gray-100 text-gray-800',
                                    'MAINTENANCE' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $counterStatusColors[$counter->status] ?? 'bg-gray-100' }}">
                                {{ $counter->status }}
                            </span>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ $counter->users->count() }} staff aktif
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('queue.create') }}" class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center">
                <i class="fas fa-plus-circle text-blue-600 text-2xl mb-2"></i>
                <p class="font-medium text-blue-700">Ambil Antrian</p>
            </a>
            <a href="{{ route('queue.tickets') }}" class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center">
                <i class="fas fa-list text-green-600 text-2xl mb-2"></i>
                <p class="font-medium text-green-700">Lihat Antrian</p>
            </a>
            <a href="{{ route('reports.daily') }}" class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-center">
                <i class="fas fa-chart-bar text-purple-600 text-2xl mb-2"></i>
                <p class="font-medium text-purple-700">Laporan Harian</p>
            </a>
            <a href="{{ route('settings.index') }}" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg p-4 text-center">
                <i class="fas fa-cog text-gray-600 text-2xl mb-2"></i>
                <p class="font-medium text-gray-700">Pengaturan</p>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Queue Chart
    const ctx = document.getElementById('queueChart').getContext('2d');
    const queueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Jumlah Antrian',
                data: @json($chartData['data']),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Auto refresh every 30 seconds
    setInterval(function() {
        $.ajax({
            url: '{{ route("dashboard.stats") }}',
            method: 'GET',
            success: function(data) {
                // Update stats cards
                $('.stats-card').each(function() {
                    const statType = $(this).data('stat');
                    if (data[statType]) {
                        $(this).find('.stat-value').text(data[statType]);
                    }
                });
                
                // Update chart
                if (data.chart_data) {
                    queueChart.data.labels = data.chart_data.labels;
                    queueChart.data.datasets[0].data = data.chart_data.data;
                    queueChart.update();
                }
            }
        });
    }, 30000);
</script>
@endpush
@endsection
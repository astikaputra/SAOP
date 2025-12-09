{{-- resources/views/loket/index.blade.php --}}
@php
    // Pastikan variabel ada dengan nilai default
    $currentTicket = $currentTicket ?? null;
    $counter = $counter ?? null;
    $waitingTickets = $waitingTickets ?? collect([]);
    $recentCalled = $recentCalled ?? collect([]);
    $stats = $stats ?? ['called_today' => 0, 'completed_today' => 0, 'avg_wait_time' => 0];
    $availableDrivers = $availableDrivers ?? collect([]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Loket') }} - <span class="text-blue-600">{{ $counter->name ?? 'Loket' }}</span>
            </h2>
            <div class="flex space-x-2">
                @if($currentTicket)
                    <button onclick="completeCurrent()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-check mr-2"></i> Selesaikan
                    </button>
                    <button onclick="skipCurrent()" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-forward mr-2"></i> Lewati
                    </button>
                @else
                    <button onclick="callNext()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-bullhorn mr-2"></i> Panggil Berikutnya
                    </button>
                @endif
            <a href="{{ route('queue.create') }}" 
            class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg flex flex-col items-center justify-center">
                <i class="fas fa-plus text-lg mb-1"></i>
                <span class="text-xs">Antrian Baru</span>
            </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Bar -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Counter Status -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Status Loket</p>
                            <div class="flex items-center mt-1">
                                <div class="w-3 h-3 rounded-full mr-2 
                                    {{ auth()->user()->counter_status == 'AVAILABLE' ? 'bg-green-500' : 
                                       (auth()->user()->counter_status == 'BUSY' ? 'bg-yellow-500' :
                                       (auth()->user()->counter_status == 'BREAK' ? 'bg-blue-500' : 'bg-gray-500')) }}"></div>
                                <p class="font-semibold">{{ auth()->user()->counter_status }}</p>
                            </div>
                        </div>
                        <div class="dropdown relative">
                            <button onclick="toggleStatusMenu()" 
                                    class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="statusMenu" class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden">
                                <form id="statusForm">
                                    @csrf
                                    <button type="button" onclick="updateStatus('AVAILABLE')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i> Tersedia
                                    </button>
                                    <button type="button" onclick="updateStatus('BUSY')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-clock text-yellow-500 mr-2"></i> Sibuk
                                    </button>
                                    <button type="button" onclick="updateStatus('BREAK')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-coffee text-blue-500 mr-2"></i> Istirahat
                                    </button>
                                    <button type="button" onclick="updateStatus('OFFLINE')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-power-off text-gray-500 mr-2"></i> Offline
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Called -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <i class="fas fa-bullhorn text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Dipanggil Hari Ini</p>
                            <p class="text-xl font-bold">{{ $stats['called_today'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Completed Today -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Selesai Hari Ini</p>
                            <p class="text-xl font-bold">{{ $stats['completed_today'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Average Wait Time -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <i class="fas fa-clock text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Rata-rata Tunggu</p>
                            <p class="text-xl font-bold">{{ $stats['avg_wait_time'] ?? 0 }} menit</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Current Ticket & Waiting Queue -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Current Ticket -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                                Antrian yang Sedang Dilayani
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($currentTicket)
                            <div class="text-center p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                                <!-- Ticket Number -->
                                <div class="text-5xl font-bold text-blue-800 mb-2">
                                    {{ $currentTicket->queue_number }}
                                </div>
                                <div class="text-lg font-semibold text-blue-600 mb-1">
                                    {{ $currentTicket->ticket_number }}
                                </div>
                                
                                <!-- Customer Info -->
                                <div class="mt-4">
                                    <div class="text-xl font-semibold text-gray-800">
                                        {{ $currentTicket->customer_name }}
                                    </div>
                                    <div class="text-gray-600">
                                        {{ $currentTicket->customer_phone }}
                                    </div>
                                </div>
                                
                                <!-- Service Info -->
                                <div class="mt-4">
                                    <div class="inline-block bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $currentTicket->service->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ $currentTicket->service->sub_type ?? '' }}
                                    </div>
                                </div>
                                
                                <!-- Timestamps -->
                                <div class="mt-6 grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div>
                                        <div class="font-medium">Ambil Antrian</div>
                                        <div>{{ $currentTicket->created_at->format('H:i') }}</div>
                                    </div>
                                    <div>
                                        <div class="font-medium">Dipanggil</div>
                                        <div>{{ $currentTicket->called_at ? $currentTicket->called_at->format('H:i') : '-' }}</div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="mt-6 flex justify-center space-x-3">
                                    <button onclick="completeCurrent()" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium flex items-center">
                                        <i class="fas fa-check mr-2"></i> Selesaikan
                                    </button>
                                    <button onclick="skipCurrent()" 
                                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-medium flex items-center">
                                        <i class="fas fa-forward mr-2"></i> Lewati
                                    </button>
                                    <button onclick="recallCurrent()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium flex items-center">
                                        <i class="fas fa-redo mr-2"></i> Panggil Ulang
                                    </button>
                                </div>
                                
                                <!-- Additional Info -->
                                @if($currentTicket->notes ?? false)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                    <div class="text-sm font-medium text-yellow-800">Catatan:</div>
                                    <div class="text-sm text-yellow-700">{{ $currentTicket->notes }}</div>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="text-center py-10">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user-clock text-gray-300 text-3xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-600 mb-2">Tidak ada antrian aktif</h4>
                                <p class="text-gray-500 mb-6">Mulai layani antrian berikutnya</p>
                                <button onclick="callNext()" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium flex items-center mx-auto">
                                    <i class="fas fa-bullhorn mr-2"></i> Panggil Antrian Berikutnya
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Waiting Queue -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-list-ol text-green-600 mr-2"></i>
                                Daftar Antrian Menunggu
                                <span class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                    {{ $waitingTickets->count() }} antrian
                                </span>
                            </h3>
                            <div class="flex space-x-2">
                                <button onclick="refreshQueue()" 
                                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded flex items-center">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                                </button>
                            </div>
                        </div>
                        
                        <div class="overflow-y-auto max-h-96">
                            @if($waitingTickets->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Antrian</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pelanggan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Ambil</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($waitingTickets as $index => $ticket)
                                    <tr class="hover:bg-gray-50 {{ $ticket->is_priority ?? false ? 'bg-yellow-50' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-bold text-lg text-center {{ ($ticket->is_priority ?? false) ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $ticket->queue_number }}
                                            </div>
                                            <div class="text-xs text-gray-500 text-center">{{ $ticket->ticket_number }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $ticket->customer_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $ticket->customer_phone }}</div>
                                            @if($ticket->is_priority ?? false)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                <i class="fas fa-star mr-1"></i> Prioritas
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $ticket->service->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->service->sub_type ?? '' }}</div>
                                            @if(($ticket->passenger_count ?? 0) > 1)
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-users mr-1"></i>{{ $ticket->passenger_count }} orang
                                            </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ticket->created_at->format('H:i') }}
                                            <div class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="callSpecificTicket('{{ $ticket->id }}')" 
                                                        class="text-blue-600 hover:text-blue-900" 
                                                        title="Panggil antrian ini">
                                                    <i class="fas fa-bullhorn"></i>
                                                </button>
                                                <button onclick="viewTicket('{{ $ticket->id }}')" 
                                                        class="text-gray-600 hover:text-gray-900"
                                                        title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="skipTicket('{{ $ticket->id }}')" 
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Lewati antrian">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="text-center py-10">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                <p class="text-gray-500 font-medium">Tidak ada antrian menunggu</p>
                                <p class="text-sm text-gray-400 mt-1">Semua antrian telah dilayani</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-6">
                    <!-- Counter Info -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-store-alt text-purple-600 mr-2"></i>
                                Informasi Loket
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama Loket:</span>
                                    <span class="font-medium">{{ $counter->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kode:</span>
                                    <span class="font-medium">{{ $counter->code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Lokasi:</span>
                                    <span class="font-medium">{{ $counter->location ?? 'Pelabuhan Utama' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Staff:</span>
                                    <span class="font-medium">{{ auth()->user()->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Layanan:</span>
                                    <div class="text-right">
                                        @php
                                            $serviceTypes = $counter->getServiceTypesSafe();
                                        @endphp
                                        @foreach($serviceTypes as $type)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded ml-1 mb-1">
                                                {{ $type }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recently Called -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-history text-orange-600 mr-2"></i>
                                Baru Saja Dipanggil
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @if($recentCalled && $recentCalled->count() > 0)
                                    @foreach($recentCalled as $ticket)
                                    <div class="flex items-start p-2 hover:bg-gray-50 rounded">
                                        <div class="flex-shrink-0 mt-1">
                                            @if($ticket->status == 'CALLED')
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-bullhorn text-yellow-600 text-xs"></i>
                                            </div>
                                            @elseif($ticket->status == 'SERVING')
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user-cog text-blue-600 text-xs"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-sm">{{ $ticket->queue_number }}</span>
                                                <span class="text-xs text-gray-500">{{ $ticket->called_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-xs text-gray-600 truncate">{{ $ticket->customer_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->service->name ?? '' }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-gray-500 text-sm">Belum ada antrian dipanggil</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Available Drivers -->
                    @if($availableDrivers && $availableDrivers->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-car text-green-600 mr-2"></i>
                                Driver Tersedia
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2">
                                @foreach($availableDrivers as $driver)
                                <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-car text-green-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-sm">{{ $driver->user->name ?? 'Driver' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $driver->vehicle_type ?? 'Motor' }} - {{ $driver->vehicle_plate ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-bolt text-red-600 mr-2"></i>
                                Aksi Cepat
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('queue.create') }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg flex flex-col items-center justify-center">
                                    <i class="fas fa-plus text-lg mb-1"></i>
                                    <span class="text-xs">Antrian Baru</span>
                                </a>
                                
                                <button onclick="printTodayReport()" 
                                        class="bg-purple-500 hover:bg-purple-600 text-white p-3 rounded-lg flex flex-col items-center justify-center">
                                    <i class="fas fa-print text-lg mb-1"></i>
                                    <span class="text-xs">Laporan</span>
                                </button>
                                
                                <button onclick="openAnnouncement()" 
                                        class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-lg flex flex-col items-center justify-center">
                                    <i class="fas fa-bullhorn text-lg mb-1"></i>
                                    <span class="text-xs">Pengumuman</span>
                                </button>
                                
                                <button onclick="openSettings()" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white p-3 rounded-lg flex flex-col items-center justify-center">
                                    <i class="fas fa-cog text-lg mb-1"></i>
                                    <span class="text-xs">Pengaturan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="skipModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lewati Antrian</h3>
                
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan (Opsional)
                    </label>
                    <textarea id="skipReason" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                              placeholder="Masukkan alasan melewati antrian..."></textarea>
                </div>
                
                <div class="mt-4 flex justify-end space-x-3">
                    <button onclick="closeSkipModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button onclick="confirmSkip()" 
                            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Lewati
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentTicketId = '{{ $currentTicket->id ?? "" }}';
        let skipTicketId = null;
        
        // Auto-refresh every 30 seconds
        let refreshInterval = setInterval(refreshQueueData, 30000);
        
        function refreshQueueData() {
            fetch('{{ route("loket.queue-data") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update waiting count if changed
                        const waitingCount = document.querySelector('.waiting-count');
                        if (waitingCount) {
                            waitingCount.textContent = data.data.waiting_count;
                        }
                        
                        // If no current ticket but now there is one, refresh page
                        if (!currentTicketId && data.data.current_ticket) {
                            location.reload();
                        }
                    }
                });
        }
        
        function toggleStatusMenu() {
            const menu = document.getElementById('statusMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('statusMenu');
            const button = document.querySelector('.dropdown button');
            
            if (menu && !menu.contains(event.target) && button && !button.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
        
        function updateStatus(status) {
            fetch('{{ route("loket.update-status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ counter_status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Status berhasil diperbarui');
                    document.getElementById('statusMenu').classList.add('hidden');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error('Gagal memperbarui status');
                }
            });
        }
        
        function callNext() {
            fetch('{{ route("loket.call-next") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    playCallSound(data.ticket.queue_number);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Terjadi kesalahan saat memanggil antrian');
            });
        }
        
        function callSpecificTicket(ticketId) {
            if (!confirm('Panggil antrian ini?')) return;
            
            fetch(`/loket/call-specific/${ticketId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    playCallSound(data.ticket.queue_number);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.message);
                }
            });
        }
        
        function completeCurrent() {
            if (!confirm('Tandai antrian ini sebagai selesai?')) return;
            
            fetch('{{ route("loket.complete-current") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            });
        }
        
        function skipCurrent() {
            skipTicketId = currentTicketId;
            document.getElementById('skipModal').classList.remove('hidden');
        }
        
        function skipTicket(ticketId) {
            skipTicketId = ticketId;
            document.getElementById('skipModal').classList.remove('hidden');
        }
        
        function closeSkipModal() {
            document.getElementById('skipModal').classList.add('hidden');
            skipTicketId = null;
            document.getElementById('skipReason').value = '';
        }
        
        function confirmSkip() {
            const reason = document.getElementById('skipReason').value;
            
            const url = skipTicketId === currentTicketId 
                ? '{{ route("loket.skip-current") }}'
                : `/loket/skip-ticket/${skipTicketId}`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeSkipModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            });
        }
        
        function recallCurrent() {
            if (!currentTicketId) return;
            
            fetch(`/loket/recall-ticket/${currentTicketId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    playCallSound('{{ $currentTicket->queue_number ?? "" }}');
                } else {
                    toastr.error(data.message);
                }
            });
        }
        
        function playCallSound(queueNumber) {
            if ('speechSynthesis' in window) {
                const speech = new SpeechSynthesisUtterance();
                speech.text = `Nomor antrian ${queueNumber.split('').join(' ')}, silakan menuju loket {{ $counter->name ?? "Loket" }}`;
                speech.lang = 'id-ID';
                speech.rate = 0.9;
                speech.volume = 1;
                
                // Play three times
                for (let i = 0; i < 3; i++) {
                    setTimeout(() => {
                        window.speechSynthesis.speak(speech);
                    }, i * 2000);
                }
            }
        }
        
        function viewTicket(ticketId) {
            window.open(`/queue/ticket/${ticketId}`, '_blank');
        }
        
        function refreshQueue() {
            location.reload();
        }
        
        function printTodayReport() {
            alert('Fitur laporan akan datang di versi berikutnya');
        }
        
        function openAnnouncement() {
            alert('Fitur pengumuman akan datang di versi berikutnya');
        }
        
        function openSettings() {
            alert('Fitur pengaturan akan datang di versi berikutnya');
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // F1 - Call next
            if (event.key === 'F1') {
                event.preventDefault();
                if (!currentTicketId) callNext();
            }
            // F2 - Complete current
            else if (event.key === 'F2' && currentTicketId) {
                event.preventDefault();
                completeCurrent();
            }
            // F3 - Skip current
            else if (event.key === 'F3' && currentTicketId) {
                event.preventDefault();
                skipCurrent();
            }
            // F5 - Refresh
            else if (event.key === 'F5') {
                event.preventDefault();
                refreshQueue();
            }
        });
    </script>
    
    <style>
        .dropdown-menu {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        tr:hover {
            transition: background-color 0.2s ease;
        }
    </style>
    @endpush
</x-app-layout>

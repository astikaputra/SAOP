<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Antrian') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('queue.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    <i class="fas fa-plus mr-1"></i> Ambil Antrian
                </a>
                <a href="{{ route('queue.public-create') }}" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    <i class="fas fa-external-link-alt mr-1"></i> Public Form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('queue.tickets') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="WAITING" {{ request('status') == 'WAITING' ? 'selected' : '' }}>Menunggu</option>
                                <option value="CALLED" {{ request('status') == 'CALLED' ? 'selected' : '' }}>Dipanggil</option>
                                <option value="SERVING" {{ request('status') == 'SERVING' ? 'selected' : '' }}>Sedang Dilayani</option>
                                <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        
                        <!-- Service Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
                            <select name="service_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Layanan</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ $service->type }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Counter Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loket</label>
                            <select name="counter_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Loket</option>
                                @foreach($counters as $counter)
                                <option value="{{ $counter->id }}" {{ request('counter_id') == $counter->id ? 'selected' : '' }}>
                                    {{ $counter->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Search -->
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari berdasarkan nomor tiket, nama, atau telepon..."
                                   class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Actions -->
                        <div class="md:col-span-1 flex items-end space-x-2">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('queue.tickets') }}" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg text-center">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $tickets->total() }}</div>
                        <div class="text-sm text-gray-600">Total Antrian</div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $tickets->where('status', 'WAITING')->count() }}</div>
                        <div class="text-sm text-gray-600">Menunggu</div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $tickets->whereIn('status', ['CALLED', 'SERVING'])->count() }}</div>
                        <div class="text-sm text-gray-600">Dipanggil</div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $tickets->where('status', 'COMPLETED')->count() }}</div>
                        <div class="text-sm text-gray-600">Selesai</div>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $tickets->where('status', 'CANCELLED')->count() }}</div>
                        <div class="text-sm text-gray-600">Dibatalkan</div>
                    </div>
                </div>
            </div>

            <!-- Queue Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tiket
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pelanggan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Layanan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loket
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <!-- Ticket Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono font-bold text-gray-900">{{ $ticket->ticket_number }}</div>
                                    <div class="text-sm text-gray-500">No. {{ $ticket->queue_number }}</div>
                                </td>
                                
                                <!-- Customer Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $ticket->customer_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $ticket->customer_phone }}</div>
                                    @if($ticket->destination)
                                    <div class="text-xs text-gray-400">Tujuan: {{ $ticket->destination }}</div>
                                    @endif
                                </td>
                                
                                <!-- Service Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $ticket->service->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $ticket->service->type_name ?? '' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $ticket->passenger_count }} orang
                                        @if($ticket->estimated_price)
                                        • Rp {{ number_format($ticket->estimated_price, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'WAITING' => 'bg-yellow-100 text-yellow-800',
                                            'CALLED' => 'bg-blue-100 text-blue-800',
                                            'SERVING' => 'bg-purple-100 text-purple-800',
                                            'COMPLETED' => 'bg-green-100 text-green-800',
                                            'CANCELLED' => 'bg-red-100 text-red-800',
                                            'NO_SHOW' => 'bg-gray-100 text-gray-800'
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100' }}">
                                        {{ $statuses[$ticket->status] ?? $ticket->status }}
                                    </span>
                                    @if($ticket->called_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Dipanggil: {{ $ticket->called_at->format('H:i') }}
                                    </div>
                                    @endif
                                </td>
                                
                                <!-- Counter -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ticket->counter)
                                    <div class="text-sm text-gray-900">{{ $ticket->counter->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $ticket->counter->location }}</div>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                
                                <!-- Time -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $ticket->created_at->format('d/m/Y') }}</div>
                                    <div>{{ $ticket->created_at->format('H:i') }}</div>
                                    <div class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</div>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('queue.show', $ticket) }}" class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($ticket->status == 'WAITING')
                                        <button onclick="callTicket({{ $ticket->id }})" class="text-green-600 hover:text-green-900" title="Panggil">
                                            <i class="fas fa-bullhorn"></i>
                                        </button>
                                        @endif
                                        
                                        @if(in_array($ticket->status, ['CALLED', 'SERVING']))
                                        <button onclick="completeTicket({{ $ticket->id }})" class="text-green-600 hover:text-green-900" title="Selesaikan">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="skipTicket({{ $ticket->id }})" class="text-yellow-600 hover:text-yellow-900" title="Lewati">
                                            <i class="fas fa-forward"></i>
                                        </button>
                                        @endif
                                        
                                        @if(!in_array($ticket->status, ['COMPLETED', 'CANCELLED']))
                                        <button onclick="cancelTicket({{ $ticket->id }})" class="text-red-600 hover:text-red-900" title="Batalkan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        
                                        <a href="{{ route('queue.print', $ticket) }}" target="_blank" class="text-gray-600 hover:text-gray-900" title="Cetak">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                    <p>Tidak ada data antrian</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tickets->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
    <!-- Call Ticket Modal -->
    <div id="callModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Panggil Antrian</h3>
                <form id="callForm">
                    @csrf
                    <input type="hidden" name="ticket_id" id="callTicketId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Loket</label>
                        <select name="counter_id" id="counterSelect" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">-- Pilih Loket --</option>
                            @foreach($counters->where('is_active', true) as $counter)
                            <option value="{{ $counter->id }}">{{ $counter->name }} ({{ $counter->location }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeCallModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Panggil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Ticket Modal -->
    <div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Selesaikan Antrian</h3>
                <form id="completeForm">
                    @csrf
                    <input type="hidden" name="ticket_id" id="completeTicketId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Driver (Opsional)</label>
                        <select name="driver_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Tanpa Driver --</option>
                            @foreach(\App\Models\Driver::with('user')->get() as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->user->name }} ({{ $driver->vehicle_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga Akhir (Rp)</label>
                        <input type="number" name="final_price" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Biarkan kosong untuk harga estimasi">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Ticket Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Batalkan Antrian</h3>
                <form id="cancelForm">
                    @csrf
                    <input type="hidden" name="ticket_id" id="cancelTicketId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                        <textarea name="reason" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ketikkan alasan pembatalan..." required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeCancelModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Batalkan Antrian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Skip Ticket Modal -->
    <div id="skipModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lewati Antrian</h3>
                <form id="skipForm">
                    @csrf
                    <input type="hidden" name="ticket_id" id="skipTicketId">
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Antrian akan dikembalikan ke daftar tunggu.</p>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan (Opsional)</label>
                        <textarea name="reason" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Alasan melewati antrian..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeSkipModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            Lewati
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush

    @push('scripts')
    <script>
        // Call Ticket
        function callTicket(ticketId) {
            document.getElementById('callTicketId').value = ticketId;
            document.getElementById('callModal').classList.remove('hidden');
        }
        
        function closeCallModal() {
            document.getElementById('callModal').classList.add('hidden');
            document.getElementById('callForm').reset();
        }
        
        document.getElementById('callForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const ticketId = formData.get('ticket_id');
            
            fetch(`/queue/${ticketId}/call`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeCallModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Terjadi kesalahan');
            });
        });
        
        // Complete Ticket
        function completeTicket(ticketId) {
            document.getElementById('completeTicketId').value = ticketId;
            document.getElementById('completeModal').classList.remove('hidden');
        }
        
        function closeCompleteModal() {
            document.getElementById('completeModal').classList.add('hidden');
            document.getElementById('completeForm').reset();
        }
        
        document.getElementById('completeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const ticketId = formData.get('ticket_id');
            
            fetch(`/queue/${ticketId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeCompleteModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Terjadi kesalahan');
            });
        });
        
        // Cancel Ticket
        function cancelTicket(ticketId) {
            if (!confirm('Yakin ingin membatalkan antrian ini?')) return;
            
            document.getElementById('cancelTicketId').value = ticketId;
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            document.getElementById('cancelForm').reset();
        }
        
        document.getElementById('cancelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const ticketId = formData.get('ticket_id');
            
            fetch(`/queue/${ticketId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeCancelModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Terjadi kesalahan');
            });
        });
        
        // Skip Ticket
        function skipTicket(ticketId) {
            document.getElementById('skipTicketId').value = ticketId;
            document.getElementById('skipModal').classList.remove('hidden');
        }
        
        function closeSkipModal() {
            document.getElementById('skipModal').classList.add('hidden');
            document.getElementById('skipForm').reset();
        }
        
        document.getElementById('skipForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const ticketId = formData.get('ticket_id');
            
            fetch(`/queue/${ticketId}/skip`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
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
            })
            .catch(error => {
                toastr.error('Terjadi kesalahan');
            });
        });
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (!document.hidden) {
                // Only refresh if we're on the first page
                const urlParams = new URLSearchParams(window.location.search);
                const page = urlParams.get('page');
                
                if (!page || page === '1') {
                    window.location.reload();
                }
            }
        }, 30000);
    </script>
    @endpush
</x-app-layout>
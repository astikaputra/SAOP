<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Loket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Counter Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $counter->name }}</h3>
                            <p class="text-gray-600">{{ $counter->location }}</p>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $counter->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 
                                       ($counter->status === 'MAINTENANCE' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $counter->status }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Staff</p>
                            <p class="font-semibold">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Debug Info -->
                    <div class="mt-4 p-3 bg-gray-50 rounded text-sm">
                        <p>Service Types: {{ json_encode($counter->service_types) }}</p>
                        <p>Service Types (Raw): {{ $counter->getRawOriginal('service_types') ?? 'NULL' }}</p>
                    </div>
                </div>
            </div>

            <!-- Waiting Queue -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Antrian Menunggu</h3>
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ $waitingTickets->count() }} antrian
                        </span>
                    </div>
                    
                    @if($waitingTickets->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Antrian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($waitingTickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-center">{{ $ticket->queue_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $ticket->customer_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ticket->service->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada antrian menunggu</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada antrian untuk layanan di loket ini.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Status Loket</h3>
                    <div class="space-y-2">
                        <button class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded">Tersedia</button>
                        <button class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded">Sibuk</button>
                        <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded">Istirahat</button>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
                    <div class="space-y-2">
                        <button class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-2 rounded">Panggil Antrian</button>
                        <button class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 rounded">Ambil Antrian Baru</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
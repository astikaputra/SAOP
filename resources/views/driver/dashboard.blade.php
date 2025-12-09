<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard Driver') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Selamat datang, {{ $driver->name }}!
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    <i class="fas fa-car mr-1"></i> 
                    {{ $driver->vehicle_number ?? 'Belum diatur' }}
                </span>
                <div class="relative">
                    <img src="{{ $driver->photo ? asset('storage/' . $driver->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($driver->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                         alt="Driver Photo" 
                         class="w-10 h-10 rounded-full object-cover">
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Today's Tasks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-tasks text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Tugas Hari Ini</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $todayTasks }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Today -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Selesai Hari Ini</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $todayCompleted }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Menunggu</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $todayPending }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Task & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Current Task -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-car mr-2"></i> Tugas Saat Ini
                                </h3>
                                @if($currentTask)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ strtoupper($currentTask->status) }}
                                    </span>
                                @endif
                            </div>

                            @if($currentTask)
                                <div class="space-y-4">
                                    <!-- Task Info -->
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">No. Tiket</p>
                                                <p class="font-medium">{{ $currentTask->ticket_number }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Layanan</p>
                                                <p class="font-medium">{{ $currentTask->service->name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Nama Pelanggan</p>
                                                <p class="font-medium">{{ $currentTask->customer_name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Telepon</p>
                                                <p class="font-medium">{{ $currentTask->customer_phone }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-sm text-gray-600">Lokasi Penjemputan</p>
                                                <p class="font-medium">{{ $currentTask->pickup_location }}</p>
                                            </div>
                                            @if($currentTask->destination)
                                            <div class="col-span-2">
                                                <p class="text-sm text-gray-600">Tujuan</p>
                                                <p class="font-medium">{{ $currentTask->destination }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status Actions -->
                                    <div class="border-t pt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Update Status</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @php
                                                $statusActions = [
                                                    'picked_up' => ['icon' => 'fa-user-check', 'label' => 'Penjemputan', 'color' => 'bg-blue-500 hover:bg-blue-600'],
                                                    'on_the_way' => ['icon' => 'fa-road', 'label' => 'Berangkat', 'color' => 'bg-yellow-500 hover:bg-yellow-600'],
                                                    'arrived' => ['icon' => 'fa-map-marker-alt', 'label' => 'Tiba', 'color' => 'bg-green-500 hover:bg-green-600'],
                                                    'completed' => ['icon' => 'fa-check-circle', 'label' => 'Selesai', 'color' => 'bg-purple-500 hover:bg-purple-600']
                                                ];
                                            @endphp

                                            @foreach($statusActions as $status => $action)
                                                <button type="button" 
                                                        onclick="updateStatus('{{ $currentTask->id }}', '{{ $status }}')"
                                                        class="px-4 py-2 {{ $action['color'] }} text-white rounded-lg font-medium text-sm flex items-center">
                                                    <i class="fas {{ $action['icon'] }} mr-2"></i>
                                                    {{ $action['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Task Details Button -->
                                    <div class="text-center">
                                        <a href="{{ route('driver.tasks.show', $currentTask->id) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Lihat Detail Tugas
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-car text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500">Tidak ada tugas aktif saat ini</p>
                                    <p class="text-sm text-gray-400 mt-2">Silakan periksa daftar tugas yang menunggu</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Pending Tasks -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-bolt mr-2"></i> Aksi Cepat
                            </h3>
                            <div class="space-y-3">
                                <a href="{{ route('driver.tasks') }}" 
                                   class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-list text-blue-600 mr-3"></i>
                                    <span>Lihat Semua Tugas</span>
                                </a>
                                <a href="{{ route('driver.history') }}" 
                                   class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    <i class="fas fa-history text-green-600 mr-3"></i>
                                    <span>Riwayat Tugas</span>
                                </a>
                                <a href="{{ route('driver.profile') }}" 
                                   class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-user text-purple-600 mr-3"></i>
                                    <span>Profil Saya</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-clock mr-2"></i> Tugas Menunggu
                            </h3>
                            @if($pendingTasks->count() > 0)
                                <div class="space-y-3">
                                    @foreach($pendingTasks as $task)
                                        <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-medium text-sm">{{ $task->customer_name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $task->service->name }}</p>
                                                </div>
                                                <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">
                                                    {{ $task->queue_number }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-600">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $task->pickup_location }}
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ route('driver.tasks.show', $task->id) }}" 
                                                   class="text-xs text-blue-600 hover:text-blue-800">
                                                    Lihat detail →
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($pendingTasks->count() >= 5)
                                        <div class="text-center pt-2">
                                            <a href="{{ route('driver.tasks') }}" 
                                               class="text-sm text-blue-600 hover:text-blue-800">
                                                Lihat lebih banyak →
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle text-2xl text-gray-300 mb-2"></i>
                                    <p class="text-gray-500 text-sm">Tidak ada tugas yang menunggu</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateStatus(taskId, status) {
            if (!confirm('Apakah Anda yakin ingin mengupdate status?')) {
                return;
            }
            
            $.ajax({
                url: `/driver/tasks/${taskId}/update-status`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Status berhasil diupdate');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Terjadi kesalahan saat mengupdate status');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
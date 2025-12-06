<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian - Ojek Pelabuhan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">OJEK PELABUHAN</h1>
                        <p class="text-blue-100">Sistem Antrian Digital</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold" id="current-time">--:--:--</div>
                        <div id="current-date">---</div>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">AMBIL ANTRIAN</h2>
                    <p class="text-gray-600">Pilih layanan yang Anda butuhkan</p>
                </div>
                
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    @foreach($services as $service)
                    <div class="border rounded-xl p-6 hover:shadow-lg transition-shadow cursor-pointer bg-white" onclick="selectService({{ $service->id }})">
                        <div class="text-center">
                            <div class="text-4xl mb-4">
                                @if($service->type == 'PICKUP_TRANSPORTASI')
                                🚚
                                @elseif($service->type == 'TRANSUP')
                                🚗
                                @else
                                🏍️
                                @endif
                            </div>
                            <h3 class="font-bold text-lg mb-2">{{ $service->name }}</h3>
                            <p class="text-gray-600 text-sm mb-3">{{ $service->description }}</p>
                            <div class="font-bold text-blue-600">
                                {{ $service->price_range }}
                            </div>
                            <div class="text-sm text-gray-500 mt-2">
                                Kapasitas: {{ $service->capacity }} orang
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Queue Display -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-lg mb-4 text-center">ANTRIAN SAAT INI</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="current-queues">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">--</div>
                            <div class="text-sm text-gray-600">Pickup Transportasi</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">--</div>
                            <div class="text-sm text-gray-600">Transup</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">--</div>
                            <div class="text-sm text-gray-600">Sewa Motor</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form (akan muncul setelah pilih service) -->
                <div id="queue-form" class="hidden">
                    <form id="take-queue-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="customer_name" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                <input type="tel" name="customer_phone" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Penumpang</label>
                                <input type="number" name="passenger_count" min="1" max="10" value="1" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
                                <input type="text" name="destination" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-center space-x-4">
                            <button type="button" onclick="cancelSelection()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                Ambil Antrian
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Info -->
                <div class="text-center text-sm text-gray-500 mt-8">
                    <p>⏰ Jam Operasional: 08:00 - 17:00 WIB</p>
                    <p class="mt-2">📞 Informasi: (021) 1234-5678</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update waktu
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = 
                now.toLocaleTimeString('id-ID');
            document.getElementById('current-date').textContent = 
                now.toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
        }
        
        // Pilih service
        let selectedServiceId = null;
        
        function selectService(serviceId) {
            selectedServiceId = serviceId;
            document.getElementById('queue-form').classList.remove('hidden');
            window.scrollTo({ top: document.getElementById('queue-form').offsetTop, behavior: 'smooth' });
        }
        
        function cancelSelection() {
            selectedServiceId = null;
            document.getElementById('queue-form').classList.add('hidden');
            document.getElementById('take-queue-form').reset();
        }
        
        // Form submit
        document.getElementById('take-queue-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('service_id', selectedServiceId);
            
            try {
                const response = await fetch('/api/queue/take', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Tiket berhasil dibuat: ${data.ticket.ticket_number}\nNomor antrian: ${data.ticket.queue_number}`);
                    cancelSelection();
                    fetchCurrentQueues();
                } else {
                    alert('Gagal membuat tiket: ' + data.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
        });
        
        // Fetch current queues
        async function fetchCurrentQueues() {
            try {
                const response = await fetch('/api/queue/current');
                const data = await response.json();
                
                if (data.success) {
                    // Update display
                    // Implementasikan sesuai kebutuhan
                }
            } catch (error) {
                console.error('Error fetching queues:', error);
            }
        }
        
        // Initialize
        updateTime();
        setInterval(updateTime, 1000);
        fetchCurrentQueues();
        setInterval(fetchCurrentQueues, 30000);
    </script>
</body>
</html>
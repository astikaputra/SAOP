<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ambil Antrian Baru') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('loket.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <form id="queueForm" method="POST" action="{{ route('queue.store') }}">
                        @csrf
                        
                        <!-- Hidden fields -->
                        <input type="hidden" name="counter_id" value="{{ $counter->id }}">
                        
                        <!-- Progress Steps -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center mb-2">
                                            <span>1</span>
                                        </div>
                                        <span class="text-sm font-medium text-blue-600">Pilih Layanan</span>
                                    </div>
                                </div>
                                <div class="flex-1 border-t-2 border-blue-600"></div>
                                <div class="flex-1">
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center mb-2">
                                            <span>2</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-500">Data Pelanggan</span>
                                    </div>
                                </div>
                                <div class="flex-1 border-t-2 border-gray-300"></div>
                                <div class="flex-1">
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center mb-2">
                                            <span>3</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-500">Konfirmasi</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Service Selection -->
                        <div id="step1" class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Layanan</h3>
                            
                            <!-- Service Type Tabs -->
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8">
                                    @foreach($servicesByType as $type => $services)
                                    <button type="button" 
                                            class="service-type-tab py-4 px-1 border-b-2 font-medium text-sm {{ $loop->first ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500' }}"
                                            id="tab-{{ strtolower(str_replace('_', '-', $type)) }}">
                                        @switch($type)
                                            @case('PICKUP_TRANSPORTASI')
                                                <i class="fas fa-truck mr-2"></i> Pickup Transportasi
                                                @break
                                            @case('TRANSUP')
                                                <i class="fas fa-car mr-2"></i> Transup
                                                @break
                                            @case('SEWA_MOTOR')
                                                <i class="fas fa-motorcycle mr-2"></i> Sewa Motor
                                                @break
                                        @endswitch
                                    </button>
                                    @endforeach
                                </nav>
                            </div>

                            <!-- Services Grid -->
                            <div id="servicesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                                <!-- Services will be loaded here -->
                                @foreach($servicesByType->first() as $service)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer service-card" 
                                     data-service-id="{{ $service->id }}"
                                     data-service-type="{{ $service->type }}">
                                    <div class="text-center">
                                        <div class="text-3xl mb-3">
                                            {!! $service->icon ? "<i class='{$service->icon}'></i>" : '📋' !!}
                                        </div>
                                        <h4 class="font-bold text-gray-800 mb-1">{{ $service->name }}</h4>
                                        <p class="text-sm text-gray-600 mb-3">{{ $service->description ?? '-' }}</p>
                                        <div class="font-bold text-blue-600">
                                            Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                        </div>
                                        @if($service->price_per_km)
                                        <div class="text-xs text-gray-500 mt-1">
                                            + Rp {{ number_format($service->price_per_km, 0, ',', '.') }}/km
                                        </div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-2">
                                            Kapasitas: {{ $service->capacity }} orang
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Selected Service Info -->
                            <div id="selectedServiceInfo" class="hidden mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-blue-800" id="selectedServiceName">-</h4>
                                        <p class="text-blue-700" id="selectedServiceDescription">-</p>
                                        <div class="mt-2">
                                            <span class="font-bold text-lg text-blue-900" id="selectedServicePrice">-</span>
                                            <span class="text-sm text-blue-700 ml-2" id="selectedServiceExtra"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" id="deselectServiceBtn" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="service_id" id="selectedServiceId">
                            </div>

                            <div class="flex justify-between mt-8">
                                <a href="{{ route('loket.index') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium">
                                    <i class="fas fa-times mr-2"></i> Batal
                                </a>
                                <button type="button" id="nextStep1Btn" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Customer Information -->
                        <div id="step2" class="hidden space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pelanggan</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Customer Name -->
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="customer_name" name="customer_name" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                           placeholder="Nama pelanggan" required>
                                    <div class="text-xs text-red-500 mt-1 hidden" id="customer_name_error"></div>
                                </div>

                                <!-- Customer Phone -->
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        No. Telepon <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" id="customer_phone" name="customer_phone" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                           placeholder="0812-3456-7890" required>
                                    <div class="text-xs text-red-500 mt-1 hidden" id="customer_phone_error"></div>
                                </div>

                                <!-- Customer Email -->
                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email (Opsional)
                                    </label>
                                    <input type="email" id="customer_email" name="customer_email" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="email@example.com">
                                </div>

                                <!-- Passenger Count -->
                                <div>
                                    <label for="passenger_count" class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah Penumpang <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex items-center">
                                        <button type="button" id="decrementPassengerBtn" class="px-4 py-3 bg-gray-200 rounded-l-lg hover:bg-gray-300 transition-colors">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" id="passenger_count" name="passenger_count" 
                                               value="1" min="1" max="10"
                                               class="w-full px-4 py-3 border-y border-gray-300 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        <button type="button" id="incrementPassengerBtn" class="px-4 py-3 bg-gray-200 rounded-r-lg hover:bg-gray-300 transition-colors">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="text-xs text-red-500 mt-1 hidden" id="passenger_count_error"></div>
                                </div>

                                <!-- Pickup Location -->
                                <div>
                                    <label for="pickup_location" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lokasi Penjemputan
                                    </label>
                                    <input type="text" id="pickup_location" name="pickup_location" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Lokasi penjemputan" value="Pelabuhan Utama">
                                </div>

                                <!-- Destination -->
                                <div>
                                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tujuan
                                    </label>
                                    <input type="text" id="destination" name="destination" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Tujuan pelanggan">
                                </div>

                                <!-- Distance (for price calculation) -->
                                <div id="distanceField" class="hidden">
                                    <label for="distance_km" class="block text-sm font-medium text-gray-700 mb-2">
                                        Jarak (km) <span class="text-gray-500">(Opsional)</span>
                                    </label>
                                    <div class="flex items-center">
                                        <input type="number" id="distance_km" name="distance_km" 
                                               step="0.1" min="0" max="100"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="0">
                                        <span class="ml-2 text-gray-600">km</span>
                                    </div>
                                </div>

                                <!-- Priority Checkbox -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="priority" name="priority" value="1"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="priority" class="font-medium text-gray-700">
                                            Antrian Prioritas
                                        </label>
                                        <p class="text-gray-500 mt-1">
                                            Untuk lansia, ibu hamil, atau disabilitas
                                        </p>
                                    </div>
                                </div>

                                <!-- Estimated Price Display -->
                                <div id="priceDisplay" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estimasi Harga
                                    </label>
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="text-2xl font-bold text-green-600" id="estimatedPriceDisplay">
                                            Rp 0
                                        </div>
                                        <div class="text-sm text-gray-600 mt-2" id="priceBreakdown">
                                            -
                                        </div>
                                    </div>
                                </div>

                                <!-- Telegram Chat ID -->
                                <div class="md:col-span-2">
                                    <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        ID Telegram (Opsional - untuk notifikasi)
                                    </label>
                                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="@username atau chat_id">
                                    <p class="text-xs text-gray-500 mt-1">
                                        Jika diisi, pelanggan akan menerima notifikasi via Telegram
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan Tambahan
                                    </label>
                                    <textarea id="notes" name="notes" rows="3" 
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Catatan khusus..."></textarea>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" id="prevStep2Btn" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                                </button>
                                <button type="button" id="nextStep2Btn"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                    Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Confirmation -->
                        <div id="step3" class="hidden space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfirmasi Antrian</h3>
                            
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Service Info -->
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-3 text-lg">Layanan</h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Jenis Layanan:</span>
                                                <span class="font-medium" id="confirmServiceType">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Nama Layanan:</span>
                                                <span class="font-medium" id="confirmServiceName">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Sub Layanan:</span>
                                                <span class="font-medium" id="confirmServiceSubType">-</span>
                                            </div>
                                            <div class="flex justify-between pt-2 border-t border-gray-200">
                                                <span class="text-gray-600">Harga:</span>
                                                <span class="font-bold text-green-600 text-lg" id="confirmPrice">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Info -->
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-3 text-lg">Pelanggan</h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Nama:</span>
                                                <span class="font-medium" id="confirmCustomerName">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Telepon:</span>
                                                <span class="font-medium" id="confirmCustomerPhone">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Penumpang:</span>
                                                <span class="font-medium" id="confirmPassengerCount">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Tujuan:</span>
                                                <span class="font-medium" id="confirmDestination">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Prioritas:</span>
                                                <span class="font-medium" id="confirmPriority">Tidak</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estimated Queue Info -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="text-center">
                                        <div class="text-sm text-gray-600">Estimasi Antrian</div>
                                        <div class="text-4xl font-bold text-blue-600 mt-2" id="estimatedQueuePosition">
                                            -
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">Nomor Antrian</div>
                                        <div class="mt-4 text-sm text-gray-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Estimasi waktu tunggu: <span id="estimatedWaitTime" class="font-medium">-</span> menit
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" id="prevStep3Btn" 
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                                </button>
                                <button type="submit" 
                                        id="submitBtn"
                                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center">
                                    <i class="fas fa-check mr-2"></i> Buat Antrian
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 transition-opacity">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white transform transition-all duration-300">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Antrian Berhasil Dibuat!</h3>
                <div class="mt-4">
                    <div class="text-2xl font-bold text-blue-600 mb-2" id="successQueueNumber">-</div>
                    <div class="text-sm text-gray-600 mb-4" id="successTicketNumber">-</div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                        <div class="text-left text-sm">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-medium" id="successCustomerName">-</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-500">Layanan:</span>
                                <span class="font-medium" id="successServiceName">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status:</span>
                                <span class="font-medium text-yellow-600">Menunggu</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button id="closeSuccessModalBtn" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 rounded-lg font-medium transition-colors">
                            Tutup
                        </button>
                        <button id="printTicketBtn" 
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg font-medium transition-colors">
                            Cetak Tiket
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        .service-type-tab {
            transition: all 0.3s ease;
        }
        
        .service-type-tab:hover {
            color: #3b82f6;
            border-color: #93c5fd;
        }
        
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        .service-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .service-card.selected {
            border-color: #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.05);
        }
        
        .border-red-500 {
            border-color: #ef4444 !important;
        }
        
        .focus\:border-red-500:focus {
            border-color: #ef4444 !important;
        }
        
        .focus\:ring-red-500:focus {
            --tw-ring-color: rgba(239, 68, 68, 0.5) !important;
        }
        
        /* Loading animation */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .fa-spinner {
            animation: spin 1s linear infinite;
        }
        
        .step-completed {
            background-color: #10b981 !important;
        }
        
        .step-current {
            background-color: #3b82f6 !important;
        }
        
        .step-pending {
            background-color: #d1d5db !important;
        }
    </style>
    
    <!-- Toastr JS dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        
        // Global variables
        let currentStep = 1;
        let selectedService = null;
        let selectedServiceType = '{{ array_keys($servicesByType->toArray())[0] ?? "PICKUP_TRANSPORTASI" }}';
        let printTicketUrl = '';
        let servicesData = {!! json_encode($servicesByType) !!};
        
        // Main initialization
        $(document).ready(function() {
            console.log('Document ready, initializing...');
            
            // Initialize everything
            initTabs();
            initServiceCards();
            initEventListeners();
            initStepIndicators();
            
            console.log('Initialization complete');
        });
        
        function initTabs() {
            // Initialize service type tabs
            $('.service-type-tab').on('click', function() {
                const type = $(this).attr('id').replace('tab-', '').replace('-', '_').toUpperCase();
                showServiceType(type);
            });
            
            // Set first tab as active
            showServiceType(selectedServiceType);
        }
        
        function initServiceCards() {
            // Add click event to service cards
            $('.service-card').on('click', function() {
                const serviceId = $(this).data('service-id');
                const serviceType = $(this).data('service-type');
                
                // Find the service data
                let serviceData = null;
                for (const [type, services] of Object.entries(servicesData)) {
                    const found = services.find(s => s.id == serviceId && s.type == serviceType);
                    if (found) {
                        serviceData = found;
                        break;
                    }
                }
                
                if (serviceData) {
                    selectService($(this), serviceData);
                } else {
                    toastr.error('Data layanan tidak ditemukan');
                }
            });
        }
        
        function initEventListeners() {
            // Navigation buttons
            $('#nextStep1Btn').on('click', function() { nextStep(); });
            $('#nextStep2Btn').on('click', function() { nextStep(); });
            $('#prevStep2Btn').on('click', function() { prevStep(); });
            $('#prevStep3Btn').on('click', function() { prevStep(); });
            
            // Deselect service button
            $('#deselectServiceBtn').on('click', function() { deselectService(); });
            
            // Passenger controls
            $('#incrementPassengerBtn').on('click', function() { incrementPassenger(); });
            $('#decrementPassengerBtn').on('click', function() { decrementPassenger(); });
            
            // Passenger count input
            $('#passenger_count').on('input', function() { validatePassengerCount(); });
            
            // Distance input for price calculation
            $('#distance_km').on('input', function() { calculatePrice(); });
            
            // Form validation on blur
            $('#customer_name, #customer_phone').on('blur', function() {
                validateField($(this));
            });
            
            // Phone number auto-format
            $('#customer_phone').on('input', function(e) {
                let value = $(this).val().replace(/\D/g, '');
                
                if (value.length > 0) {
                    if (value.length <= 4) {
                        value = value;
                    } else if (value.length <= 8) {
                        value = value.slice(0, 4) + '-' + value.slice(4);
                    } else {
                        value = value.slice(0, 4) + '-' + value.slice(4, 8) + '-' + value.slice(8, 12);
                    }
                }
                
                $(this).val(value);
            });
            
            // Success modal buttons
            $('#closeSuccessModalBtn').on('click', function() { closeSuccessModal(); });
            $('#printTicketBtn').on('click', function() { printTicket(); });
            
            // Form submission
            $('#queueForm').on('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        }
        
        function initStepIndicators() {
            updateStepIndicators();
        }
        
        function showServiceType(type) {
            console.log('Showing service type:', type);
            selectedServiceType = type;
            
            // Update active tab
            $('.service-type-tab').removeClass('border-blue-500 text-blue-600')
                                  .addClass('border-transparent text-gray-500');
            
            const typeFormatted = type.toLowerCase().replace('_', '-');
            $(`#tab-${typeFormatted}`).removeClass('border-transparent text-gray-500')
                                      .addClass('border-blue-500 text-blue-600');
            
            // Filter services
            $('.service-card').each(function() {
                if ($(this).data('service-type') === type) {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            });
            
            // Check if any services are visible
            const visibleServices = $('.service-card:not(.hidden)').length;
            const servicesGrid = $('#servicesGrid');
            let noServicesMessage = servicesGrid.find('.no-services-message');
            
            if (visibleServices === 0) {
                if (noServicesMessage.length === 0) {
                    servicesGrid.append(`
                        <div class="no-services-message col-span-3 text-center py-8">
                            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">Belum ada layanan tersedia untuk kategori ini.</p>
                        </div>
                    `);
                }
            } else {
                noServicesMessage.remove();
            }
        }
        
        function selectService(element, service) {
            console.log('Selecting service:', service);
            selectedService = service;
            
            // Remove selection from other cards
            $('.service-card').removeClass('selected');
            
            // Add selection to clicked card
            element.addClass('selected');
            
            // Update selected service info
            $('#selectedServiceName').text(service.name);
            $('#selectedServiceDescription').text(service.description || '-');
            $('#selectedServiceId').val(service.id);
            
            let priceText = `Rp ${new Intl.NumberFormat('id-ID').format(service.base_price)}`;
            $('#selectedServicePrice').text(priceText);
            
            if (service.price_per_km) {
                $('#selectedServiceExtra').text(`+ Rp ${new Intl.NumberFormat('id-ID').format(service.price_per_km)}/km`);
                $('#distanceField').removeClass('hidden');
            } else {
                $('#selectedServiceExtra').text('');
                $('#distanceField').addClass('hidden');
            }
            
            // Show selected service info
            $('#selectedServiceInfo').removeClass('hidden');
            
            // Enable next button
            $('#nextStep1Btn').prop('disabled', false);
            
            // Calculate initial price
            calculatePrice();
            
            // Validate passenger count for this service
            validatePassengerCount();
        }
        
        function deselectService() {
            console.log('Deselecting service');
            selectedService = null;
            $('#selectedServiceInfo').addClass('hidden');
            $('#selectedServiceId').val('');
            $('#nextStep1Btn').prop('disabled', true);
            $('#distanceField').addClass('hidden');
            $('#priceDisplay').addClass('hidden');
            
            // Remove selection from cards
            $('.service-card').removeClass('selected');
        }
        
        function calculatePrice() {
            if (!selectedService) {
                console.log('No service selected for price calculation');
                return;
            }
            
            let price = parseFloat(selectedService.base_price);
            let breakdown = `Harga dasar: Rp ${new Intl.NumberFormat('id-ID').format(price)}`;
            
            if (selectedService.price_per_km) {
                const distance = parseFloat($('#distance_km').val()) || 0;
                
                if (distance > 0) {
                    const distancePrice = selectedService.price_per_km * distance;
                    price += distancePrice;
                    breakdown += ` + Jarak (${distance} km × Rp ${selectedService.price_per_km.toLocaleString('id-ID')}): Rp ${distancePrice.toLocaleString('id-ID')}`;
                }
            }
            
            // Update price display
            $('#estimatedPriceDisplay').text(`Rp ${new Intl.NumberFormat('id-ID').format(price)}`);
            $('#priceBreakdown').text(breakdown);
            $('#priceDisplay').removeClass('hidden');
        }
        
        function incrementPassenger() {
            const input = $('#passenger_count');
            let value = parseInt(input.val()) || 1;
            const capacity = selectedService ? selectedService.capacity : 10;
            
            if (value < capacity) {
                input.val(value + 1);
                validatePassengerCount();
            } else {
                toastr.warning(`Maksimal ${capacity} penumpang untuk layanan ini`);
            }
        }
        
        function decrementPassenger() {
            const input = $('#passenger_count');
            let value = parseInt(input.val()) || 1;
            if (value > 1) {
                input.val(value - 1);
                validatePassengerCount();
            }
        }
        
        function validateField(field) {
            const fieldId = field.attr('id');
            const value = field.val().trim();
            const errorElement = $(`#${fieldId}_error`);
            
            if (!value) {
                field.addClass('border-red-500');
                errorElement.text('Field ini wajib diisi').removeClass('hidden');
                return false;
            } else {
                field.removeClass('border-red-500');
                errorElement.addClass('hidden');
                
                // Additional validation for phone
                if (fieldId === 'customer_phone') {
                    const phoneRegex = /^[0-9-]{10,15}$/;
                    if (!phoneRegex.test(value)) {
                        field.addClass('border-red-500');
                        errorElement.text('Format telepon tidak valid (contoh: 0812-3456-7890)').removeClass('hidden');
                        return false;
                    }
                }
            }
            return true;
        }
        
        function validatePassengerCount() {
            const field = $('#passenger_count');
            const value = parseInt(field.val()) || 1;
            const errorElement = $('#passenger_count_error');
            const capacity = selectedService ? selectedService.capacity : 10;
            
            if (value < 1 || value > capacity) {
                field.addClass('border-red-500');
                errorElement.text(`Jumlah penumpang harus antara 1-${capacity} orang`).removeClass('hidden');
                return false;
            } else {
                field.removeClass('border-red-500');
                errorElement.addClass('hidden');
            }
            return true;
        }
        
        function validateStep2() {
            console.log('Validating step 2');
            
            let isValid = true;
            const fieldsToValidate = ['customer_name', 'customer_phone'];
            
            fieldsToValidate.forEach(fieldId => {
                const field = $(`#${fieldId}`);
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!validatePassengerCount()) {
                isValid = false;
            }
            
            if (!isValid) {
                toastr.error('Harap perbaiki data yang masih error', 'Validasi Gagal');
            }
            
            return isValid;
        }
        
        function nextStep() {
            console.log('Next step clicked. Current step:', currentStep);
            
            if (currentStep === 1) {
                if (!selectedService) {
                    toastr.error('Pilih layanan terlebih dahulu');
                    return;
                }
            } else if (currentStep === 2) {
                if (!validateStep2()) {
                    return;
                }
                
                // Update confirmation data
                updateConfirmation();
                
                // Get queue estimation
                getQueueEstimation();
            }
            
            $(`#step${currentStep}`).addClass('hidden');
            currentStep++;
            $(`#step${currentStep}`).removeClass('hidden');
            updateStepIndicators();
        }
        
        function prevStep() {
            console.log('Previous step clicked. Current step:', currentStep);
            $(`#step${currentStep}`).addClass('hidden');
            currentStep--;
            $(`#step${currentStep}`).removeClass('hidden');
            updateStepIndicators();
        }
        
        function updateStepIndicators() {
            console.log('Updating step indicators for step:', currentStep);
            
            $('.flex-col.items-center').each(function(index) {
                const stepNumber = index + 1;
                const stepNumberDiv = $(this).find('div.w-10');
                const stepTextSpan = $(this).find('span.text-sm');
                
                // Reset
                stepNumberDiv.removeClass('step-completed step-current step-pending')
                              .removeClass('bg-green-500 bg-blue-600 bg-gray-300');
                stepTextSpan.removeClass('text-blue-600 text-green-600 text-gray-500');
                
                if (stepNumber < currentStep) {
                    // Step sudah diselesaikan
                    stepNumberDiv.addClass('step-completed bg-green-500')
                                  .html('<i class="fas fa-check text-sm"></i>');
                    stepTextSpan.addClass('text-green-600');
                } else if (stepNumber === currentStep) {
                    // Step aktif
                    stepNumberDiv.addClass('step-current bg-blue-600')
                                  .html(`<span>${stepNumber}</span>`);
                    stepTextSpan.addClass('text-blue-600');
                } else {
                    // Step belum aktif
                    stepNumberDiv.addClass('step-pending bg-gray-300')
                                  .html(`<span>${stepNumber}</span>`);
                    stepTextSpan.addClass('text-gray-500');
                }
            });
            
            // Update progress lines
            $('.flex-1.border-t-2').each(function(index) {
                const stepNumber = index + 1;
                
                $(this).removeClass('border-blue-600 border-green-500 border-gray-300');
                
                if (stepNumber < currentStep) {
                    $(this).addClass('border-green-500');
                } else if (stepNumber === currentStep) {
                    $(this).addClass('border-blue-600');
                } else {
                    $(this).addClass('border-gray-300');
                }
            });
        }
        
        function updateConfirmation() {
            console.log('Updating confirmation data');
            
            if (!selectedService) {
                console.error('No service selected for confirmation');
                return;
            }
            
            // Service info
            let serviceTypeText = '';
            switch(selectedService.type) {
                case 'PICKUP_TRANSPORTASI':
                    serviceTypeText = 'Pickup Transportasi';
                    break;
                case 'TRANSUP':
                    serviceTypeText = 'Transup';
                    break;
                case 'SEWA_MOTOR':
                    serviceTypeText = 'Sewa Motor';
                    break;
                default:
                    serviceTypeText = selectedService.type;
            }
            
            $('#confirmServiceType').text(serviceTypeText);
            $('#confirmServiceName').text(selectedService.name);
            $('#confirmServiceSubType').text(selectedService.sub_type || '-');
            
            // Price
            let price = parseFloat(selectedService.base_price);
            if (selectedService.price_per_km) {
                const distance = parseFloat($('#distance_km').val()) || 0;
                price += selectedService.price_per_km * distance;
            }
            $('#confirmPrice').text(`Rp ${new Intl.NumberFormat('id-ID').format(price)}`);
            
            // Customer info
            $('#confirmCustomerName').text($('#customer_name').val());
            $('#confirmCustomerPhone').text($('#customer_phone').val());
            $('#confirmPassengerCount').text($('#passenger_count').val() + ' orang');
            $('#confirmDestination').text($('#destination').val() || '-');
            $('#confirmPriority').text($('#priority').is(':checked') ? 'Ya' : 'Tidak');
        }
        
        async function getQueueEstimation() {
            console.log('Getting queue estimation');
            
            if (!selectedService) {
                console.error('No service selected for queue estimation');
                return;
            }
            
            try {
                const response = await fetch(`/api/queue/estimation?service_id=${selectedService.id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Queue estimation response:', data);
                
                if (data.success) {
                    // Format queue number dengan prefix
                    const queueNumber = data.data.queue_number;
                    const prefix = getServicePrefix(selectedService.type);
                    const formattedQueueNumber = prefix + queueNumber.toString().padStart(3, '0');
                    
                    $('#estimatedQueuePosition').text(formattedQueueNumber);
                    $('#estimatedWaitTime').text(data.data.estimated_wait_minutes || '?');
                } else {
                    console.error('Queue estimation failed:', data.message);
                    // Fallback ke default values
                    showDefaultEstimation();
                }
            } catch (error) {
                console.error('Error getting queue estimation:', error);
                // Fallback ke default values jika API error
                showDefaultEstimation();
            }
        }

        // Fungsi untuk menampilkan estimasi default
        function showDefaultEstimation() {
            if (!selectedService) return;
            
            const prefix = getServicePrefix(selectedService.type);
            const queueNum = Math.floor(Math.random() * 50) + 1;
            const formattedQueueNumber = prefix + queueNum.toString().padStart(3, '0');
            
            $('#estimatedQueuePosition').text(formattedQueueNumber);
            $('#estimatedWaitTime').text('10-15');
        }

        // Fungsi untuk mendapatkan prefix berdasarkan service type
        function getServicePrefix(serviceType) {
            const prefixes = {
                'PICKUP_TRANSPORTASI': 'PT',
                'TRANSUP': 'TS',
                'SEWA_MOTOR': 'SM'
            };
            return prefixes[serviceType] || 'Q';
        }

        // Tambahkan juga fungsi untuk updateConfirmation
        function updateConfirmation() {
            console.log('Updating confirmation data');
            
            if (!selectedService) {
                console.error('No service selected for confirmation');
                return;
            }
            
            // Service info
            let serviceTypeText = '';
            switch(selectedService.type) {
                case 'PICKUP_TRANSPORTASI':
                    serviceTypeText = 'Pickup Transportasi';
                    break;
                case 'TRANSUP':
                    serviceTypeText = 'Transup';
                    break;
                case 'SEWA_MOTOR':
                    serviceTypeText = 'Sewa Motor';
                    break;
                default:
                    serviceTypeText = selectedService.type;
            }
            
            $('#confirmServiceType').text(serviceTypeText);
            $('#confirmServiceName').text(selectedService.name);
            $('#confirmServiceSubType').text(selectedService.sub_type || '-');
            
            // Price
            let price = parseFloat(selectedService.base_price);
            if (selectedService.price_per_km) {
                const distance = parseFloat($('#distance_km').val()) || 0;
                price += selectedService.price_per_km * distance;
            }
            $('#confirmPrice').text(`Rp ${new Intl.NumberFormat('id-ID').format(price)}`);
            
            // Customer info
            $('#confirmCustomerName').text($('#customer_name').val());
            $('#confirmCustomerPhone').text($('#customer_phone').val());
            $('#confirmPassengerCount').text($('#passenger_count').val() + ' orang');
            $('#confirmDestination').text($('#destination').val() || '-');
            $('#confirmPriority').text($('#priority').is(':checked') ? 'Ya' : 'Tidak');
        }
        
        // function submitForm() {
        //     console.log('Form submitted');
            
        //     // Final validation
        //     if (currentStep !== 3) {
        //         toastr.error('Harap selesaikan semua langkah terlebih dahulu');
        //         return;
        //     }
            
        //     if (!selectedService) {
        //         toastr.error('Pilih layanan terlebih dahulu');
        //         return;
        //     }
            
        //     const formData = new FormData($('#queueForm')[0]);
        //     const submitBtn = $('#submitBtn');
            
        //     // Disable submit button
        //     submitBtn.prop('disabled', true);
        //     const originalText = submitBtn.html();
        //     submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...');
            
        //     console.log('Submitting form data...');
            
        //     $.ajax({
        //         url: $('#queueForm').attr('action'),
        //         method: 'POST',
        //         data: formData,
        //         processData: false,
        //         contentType: false,
        //         success: function(data) {
        //             console.log('Response data:', data);
        //             if (data.success) {
        //                 showSuccessModal(data.ticket, data.print_url);
        //             } else {
        //                 toastr.error(data.message || 'Terjadi kesalahan');
        //             }
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Form submission error:', error);
        //             toastr.error('Terjadi kesalahan saat membuat antrian');
        //         },
        //         complete: function() {
        //             submitBtn.prop('disabled', false);
        //             submitBtn.html(originalText);
        //         }
        //     });
        // }
        function submitForm() {
            console.log('Form submitted');
            
            // Final validation
            if (currentStep !== 3) {
                toastr.error('Harap selesaikan semua langkah terlebih dahulu');
                return;
            }
            
            if (!selectedService) {
                toastr.error('Pilih layanan terlebih dahulu');
                return;
            }
            
            const formData = new FormData($('#queueForm')[0]);
            const submitBtn = $('#submitBtn');
            
            // Disable submit button
            submitBtn.prop('disabled', true);
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...');
            
            console.log('Submitting form data...');
            
            $.ajax({
                url: $('#queueForm').attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    console.log('Response data:', data);
                    if (data.success) {
                        // Format queue number untuk display
                        if (data.ticket) {
                            const prefix = getServicePrefix(selectedService.type);
                            const formattedQueueNumber = prefix + data.ticket.queue_number.toString().padStart(3, '0');
                            data.ticket.formatted_queue_number = formattedQueueNumber;
                        }
                        showSuccessModal(data.ticket, data.print_url);
                    } else {
                        toastr.error(data.message || 'Terjadi kesalahan');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Form submission error:', error);
                    
                    // Coba parse error response
                    let errorMessage = 'Terjadi kesalahan saat membuat antrian';
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        } else if (errorData.errors) {
                            // Jika ada validation errors
                            const errors = Object.values(errorData.errors).flat();
                            errorMessage = errors.join('<br>');
                        }
                    } catch (e) {
                        // Jika response bukan JSON
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    
                    toastr.error(errorMessage);
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        }
        
        function showSuccessModal(ticket, printUrl) {
            console.log('Showing success modal for ticket:', ticket);
            printTicketUrl = printUrl;
            
            // Format queue number untuk display
            let displayQueueNumber = '-';
            if (ticket) {
                if (ticket.formatted_queue_number) {
                    displayQueueNumber = ticket.formatted_queue_number;
                } else if (ticket.queue_number) {
                    const prefix = getServicePrefix(selectedService ? selectedService.type : '');
                    displayQueueNumber = prefix + ticket.queue_number.toString().padStart(3, '0');
                }
            }
            
            $('#successQueueNumber').text(displayQueueNumber);
            $('#successTicketNumber').text(ticket.ticket_number || '-');
            $('#successCustomerName').text(ticket.customer_name || '-');
            $('#successServiceName').text(selectedService ? selectedService.name : '-');
            
            $('#successModal').removeClass('hidden');
            
            // Auto close modal after 10 seconds
            setTimeout(() => {
                if (!$('#successModal').hasClass('hidden')) {
                    closeSuccessModal();
                }
            }, 10000);
        }
        
        function closeSuccessModal() {
            $('#successModal').addClass('hidden');
            window.location.href = '{{ route("loket.index") }}';
        }
        
        function printTicket() {
            if (printTicketUrl) {
                window.open(printTicketUrl, '_blank');
            } else {
                toastr.warning('URL cetak tidak tersedia');
            }
        }
        
        function resetForm() {
            console.log('Resetting form');
            
            // Reset form fields
            $('#queueForm')[0].reset();
            
            // Reset visual selections
            deselectService();
            
            // Reset steps
            $('#step2, #step3').addClass('hidden');
            $('#step1').removeClass('hidden');
            
            // Reset step indicators
            currentStep = 1;
            updateStepIndicators();
            
            // Reset confirmation data
            ['confirmServiceType', 'confirmServiceName', 'confirmServiceSubType', 
             'confirmPrice', 'confirmCustomerName', 'confirmCustomerPhone', 
             'confirmPassengerCount', 'confirmDestination', 'confirmPriority']
            .forEach(id => {
                $(`#${id}`).text('-');
            });
            
            // Reset passenger count to default
            $('#passenger_count').val(1);
            
            // Reset error displays
            $('[id$="_error"]').addClass('hidden');
            
            console.log('Form reset complete');
        }
    </script>
</x-app-layout>
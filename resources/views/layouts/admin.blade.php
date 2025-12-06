<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Antrian Ojek Pelabuhan')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Page Content -->
        <div class="ml-64">
            <!-- Header -->
            @include('layouts.partials.header')
            
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif
            
            <!-- Main Content -->
            <main class="p-6">
                <!-- Breadcrumb -->
                @if (isset($breadcrumbs))
                    <nav class="mb-4">
                        <ol class="flex items-center space-x-2 text-sm text-gray-600">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-blue-600"><i class="fas fa-home"></i></a></li>
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="flex items-center">
                                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                                    @if (isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                    @else
                                        <span class="text-gray-800 font-medium">{{ $breadcrumb['title'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif
                
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-green-700">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <span class="text-red-700">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Pusher for real-time updates -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    
    <script>
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
        
        // CSRF Token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Initialize Pusher
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true
        });
        
        // Subscribe to queue channel
        const channel = pusher.subscribe('queue-channel');
        
        // Listen for new queue
        channel.bind('queue-created', function(data) {
            toastr.info('Antrian baru: ' + data.ticket_number);
            if (typeof updateQueueDisplay === 'function') {
                updateQueueDisplay();
            }
        });
        
        // Listen for queue called
        channel.bind('queue-called', function(data) {
            toastr.warning('Antrian dipanggil: ' + data.ticket_number);
            if (typeof updateQueueDisplay === 'function') {
                updateQueueDisplay();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
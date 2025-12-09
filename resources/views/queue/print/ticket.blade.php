<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tiket #{{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: 80mm 150mm;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="p-2">
    <div class="max-w-md mx-auto border-2 border-gray-800 p-4">
        <!-- Header -->
        <div class="text-center mb-4 border-b-2 border-gray-800 pb-3">
            <h1 class="text-xl font-bold text-gray-900">PELABUHAN OJEK TRANSPORT</h1>
            <p class="text-sm text-gray-700">Sistem Antrian Digital</p>
            <p class="text-xs text-gray-600">Jl. Pelabuhan No. 1, Kota Pelabuhan</p>
        </div>
        
        <!-- Ticket Info -->
        <div class="text-center mb-4">
            <div class="text-4xl font-bold text-gray-900 mb-1">{{ $ticket->queue_number }}</div>
            <div class="text-sm text-gray-700">Nomor Antrian</div>
            <div class="text-xs text-gray-600 mt-1">{{ $ticket->ticket_number }}</div>
        </div>
        
        <!-- Service Info -->
        <div class="mb-4 border-b border-gray-300 pb-3">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Layanan:</span>
                <span class="text-sm font-bold text-gray-900">{{ $ticket->service->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Sub Layanan:</span>
                <span class="text-sm text-gray-900">{{ $ticket->service->sub_type ?? '' }}</span>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="mb-4 border-b border-gray-300 pb-3">
            <div class="mb-2">
                <div class="text-sm font-medium text-gray-700 mb-1">Nama Pelanggan:</div>
                <div class="text-sm font-bold text-gray-900">{{ $ticket->customer_name }}</div>
            </div>
            <div class="mb-2">
                <div class="text-sm font-medium text-gray-700 mb-1">No. Telepon:</div>
                <div class="text-sm text-gray-900">{{ $ticket->customer_phone }}</div>
            </div>
            <div class="mb-2">
                <div class="text-sm font-medium text-gray-700 mb-1">Jumlah Penumpang:</div>
                <div class="text-sm text-gray-900">{{ $ticket->passenger_count }} orang</div>
            </div>
            @if($ticket->destination)
            <div class="mb-2">
                <div class="text-sm font-medium text-gray-700 mb-1">Tujuan:</div>
                <div class="text-sm text-gray-900">{{ $ticket->destination }}</div>
            </div>
            @endif
        </div>
        
        <!-- Status & Price -->
        <div class="mb-4 border-b border-gray-300 pb-3">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Status:</div>
                    <div class="text-sm font-bold text-yellow-600">MENUNGGU</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Estimasi Harga:</div>
                    <div class="text-sm font-bold text-green-600">Rp {{ number_format($ticket->total_price, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <!-- Counter Info -->
        <div class="mb-4">
            <div class="text-center">
                <div class="text-sm font-medium text-gray-700 mb-1">Loket Pelayanan:</div>
                <div class="text-lg font-bold text-blue-700">{{ $ticket->counter->name ?? 'Loket 1' }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ $ticket->counter->location ?? 'Area Utama Pelabuhan' }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center border-t-2 border-gray-800 pt-3">
            <div class="text-xs text-gray-700 mb-1">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ $ticket->created_at->format('d/m/Y H:i:s') }}
            </div>
            <p class="text-xs text-gray-600">
                <strong>Informasi:</strong> Simpan tiket ini dan tunggu panggilan di loket
            </p>
            <div class="text-xs text-gray-500 mt-2">
                Tiket ini dicetak secara otomatis
            </div>
        </div>
        
        <!-- Barcode Placeholder -->
        <div class="mt-4 text-center">
            <div class="inline-block bg-gray-100 p-2">
                <div class="text-xs text-gray-600 mb-1">Scan untuk verifikasi</div>
                <div class="text-lg font-mono">{{ $ticket->ticket_number }}</div>
            </div>
        </div>
    </div>
    
    <!-- Print Button -->
    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-print mr-2"></i> Cetak Tiket
        </button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2">
            <i class="fas fa-times mr-2"></i> Tutup
        </button>
    </div>
    
    <script>
        // Auto print after load
        window.onload = function() {
            window.print();
            
            // Auto close after 5 seconds if printed
            setTimeout(function() {
                if (!document.hidden) {
                    window.close();
                }
            }, 5000);
        };
        
        // Listen for after print
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
    </script>
</body>
</html>
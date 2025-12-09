{{-- resources/views/queue/tickets.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Antrian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-center py-8">
                        <i class="fas fa-list-alt text-5xl text-green-500 mb-4"></i>
                        <h3 class="text-2xl font-bold mb-4">Daftar Antrian</h3>
                        <p class="text-gray-600 mb-6">Fitur ini sedang dalam pengembangan</p>
                        
                        <a href="{{ route('loket.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg inline-block">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
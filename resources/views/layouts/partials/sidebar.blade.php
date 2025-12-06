<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-800">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-motorcycle text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Ojek Pelabuhan</h1>
                    <p class="text-xs text-gray-400">Antrian System</p>
                </div>
            </a>
        </div>
        
        <!-- User Profile -->
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div>
                    <p class="font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->role_name }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 p-4 overflow-y-auto">
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                @if(auth()->user()->isAdmin() || auth()->user()->isLoketStaff())
                <!-- Queue Management -->
                <li class="mt-6 mb-2">
                    <p class="text-xs uppercase text-gray-500 font-semibold px-3">Manajemen Antrian</p>
                </li>
                
                @if(auth()->user()->isLoketStaff())
                <li>
                    <a href="{{ route('loket.queue') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('loket.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-ticket-alt w-5"></i>
                        <span>Loket Antrian</span>
                    </a>
                </li>
                @endif
                
                <li>
                    <a href="{{ route('queue.tickets') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('queue.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-list w-5"></i>
                        <span>Daftar Antrian</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('queue.create') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                        <i class="fas fa-plus-circle w-5"></i>
                        <span>Ambil Antrian</span>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->isAdmin())
                <!-- Admin Management -->
                <li class="mt-6 mb-2">
                    <p class="text-xs uppercase text-gray-500 font-semibold px-3">Admin Management</p>
                </li>
                
                <li>
                    <a href="{{ route('services.index') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('services.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-concierge-bell w-5"></i>
                        <span>Layanan</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('counters.index') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('counters.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-desktop w-5"></i>
                        <span>Loket</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('users.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-users w-5"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('drivers.index') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('drivers.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-user-tie w-5"></i>
                        <span>Driver</span>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->isDriver())
                <!-- Driver Menu -->
                <li class="mt-6 mb-2">
                    <p class="text-xs uppercase text-gray-500 font-semibold px-3">Driver</p>
                </li>
                
                <li>
                    <a href="{{ route('driver.trips') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                        <i class="fas fa-road w-5"></i>
                        <span>Perjalanan Saya</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('driver.availability') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                        <i class="fas fa-toggle-on w-5"></i>
                        <span>Status Ketersediaan</span>
                    </a>
                </li>
                @endif
                
                <!-- Reports -->
                @if(auth()->user()->isAdmin())
                <li class="mt-6 mb-2">
                    <p class="text-xs uppercase text-gray-500 font-semibold px-3">Laporan</p>
                </li>
                
                <li>
                    <a href="{{ route('reports.daily') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Laporan Harian</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('reports.monthly') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                        <i class="fas fa-chart-line w-5"></i>
                        <span>Laporan Bulanan</span>
                    </a>
                </li>
                @endif
                
                <!-- Settings -->
                <li class="mt-6 mb-2">
                    <p class="text-xs uppercase text-gray-500 font-semibold px-3">Pengaturan</p>
                </li>
                
                <li>
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('settings.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-cog w-5"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('profile.edit') }}" 
                       class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('profile.*') ? 'bg-gray-800' : '' }}">
                        <i class="fas fa-user-cog w-5"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Footer -->
        <div class="p-4 border-t border-gray-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800 w-full text-left">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>
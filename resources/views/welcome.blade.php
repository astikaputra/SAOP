<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Antrian Ojek Pelabuhan</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Styles -->
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .welcome-container {
                background: white;
                border-radius: 1rem;
                padding: 3rem;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 500px;
                width: 100%;
                text-align: center;
            }
            
            .logo {
                font-size: 2.5rem;
                font-weight: 700;
                color: #4f46e5;
                margin-bottom: 1rem;
            }
            
            .logo-icon {
                display: inline-block;
                background: #4f46e5;
                color: white;
                width: 60px;
                height: 60px;
                border-radius: 12px;
                line-height: 60px;
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }
            
            .description {
                color: #6b7280;
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            
            .auth-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-block;
            }
            
            .btn-primary {
                background: #4f46e5;
                color: white;
            }
            
            .btn-primary:hover {
                background: #4338ca;
                transform: translateY(-2px);
            }
            
            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }
            
            .btn-secondary:hover {
                background: #e5e7eb;
                transform: translateY(-2px);
            }
            
            .features {
                margin-top: 2rem;
                text-align: left;
            }
            
            .feature-item {
                display: flex;
                align-items: center;
                margin-bottom: 0.75rem;
                color: #4b5563;
            }
            
            .feature-icon {
                color: #10b981;
                margin-right: 0.75rem;
            }
        </style>
    </head>
    <body>
        <div class="welcome-container">
            <div class="logo-icon">
                <i>🏍️</i>
            </div>
            <h1 class="logo">Ojek Pelabuhan</h1>
            
            <p class="description">
                Sistem antrian modern untuk layanan ojek di pelabuhan. 
                Kelola antrian dengan efisien, notifikasi real-time, 
                dan monitoring terintegrasi.
            </p>
            
            <div class="auth-buttons">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            Masuk
                        </a>
                        
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-secondary">
                                Daftar
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
            
            <div class="features">
                <h3 style="color: #4f46e5; margin-bottom: 1rem;">Fitur Utama:</h3>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Antrian real-time dengan notifikasi</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Dashboard loket dan driver</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Integrasi Telegram untuk pelanggan</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Laporan harian dan bulanan</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Monitoring posisi driver</span>
                </div>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 0.875rem;">
                <p>Login Demo:</p>
                <div style="font-size: 0.75rem; margin-top: 0.5rem;">
                    <div>Admin: admin@pelabuhan.com / password123</div>
                    <div>Loket: budi@pelabuhan.com / password123</div>
                    <div>Driver: joko@pelabuhan.com / password123</div>
                </div>
            </div>
        </div>
    </body>
</html>
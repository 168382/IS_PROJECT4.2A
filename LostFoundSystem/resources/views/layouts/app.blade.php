<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lost and Found Tracking System')</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #1a56db;
            --primary-light: #3b82f6;
            --primary-dark: #1e40af;
            --accent: #06b6d4;
            --surface: #ffffff;
            --background: #f0f4f8;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.04), 0 2px 4px rgba(0,0,0,0.03);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.06), 0 4px 10px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 40px rgba(0,0,0,0.08);
            --radius: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .main-navbar {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1050;
            transition: box-shadow 0.3s ease;
        }
        .main-navbar.scrolled { box-shadow: var(--shadow-md); }
        .navbar-brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
            margin-right: 10px;
        }
        .navbar-brand {
            font-weight: 700; font-size: 1.15rem; color: var(--text-primary) !important;
            display: flex; align-items: center;
        }
        .main-navbar .nav-link {
            font-weight: 500; color: var(--text-secondary) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .main-navbar .nav-link:hover, .main-navbar .nav-link.active {
            color: var(--primary) !important; background: rgba(26,86,219,0.06);
        }
        .btn-nav-outline {
            border: 1.5px solid var(--primary); color: var(--primary) !important;
            font-weight: 600; border-radius: 8px; padding: 0.45rem 1.25rem !important;
            transition: all 0.25s ease;
        }
        .btn-nav-outline:hover { background: var(--primary); color: white !important; }
        .btn-nav-solid {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white !important; font-weight: 600; border: none;
            border-radius: 8px; padding: 0.45rem 1.25rem !important;
            transition: all 0.25s ease; box-shadow: 0 2px 8px rgba(26,86,219,0.25);
        }
        .btn-nav-solid:hover { box-shadow: 0 4px 14px rgba(26,86,219,0.4); transform: translateY(-1px); }

        /* ── Hero ────────────────────────────────────────────── */
        .hero-section {
            background: linear-gradient(135deg, #1a56db 0%, #06b6d4 50%, #3b82f6 100%);
            position: relative; overflow: hidden;
            padding: 5rem 0;
            color: white;
        }
        .hero-section::before {
            content: '';
            position: absolute; top: -50%; right: -30%;
            width: 80%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-section::after {
            content: '';
            position: absolute; bottom: -60px; left: 0; right: 0; height: 120px;
            background: var(--background);
            clip-path: ellipse(55% 100% at 50% 100%);
        }
        .hero-title { font-size: 3rem; font-weight: 800; line-height: 1.15; letter-spacing: -0.02em; }
        .hero-subtitle { font-size: 1.15rem; opacity: 0.9; max-width: 600px; margin: 0 auto; }
        .hero-btn {
            padding: 0.8rem 2rem; font-weight: 600; border-radius: 10px;
            font-size: 1rem; transition: all 0.3s ease;
        }
        .hero-btn-white {
            background: white; color: var(--primary); border: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        .hero-btn-white:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.18); color: var(--primary-dark); }
        .hero-btn-outline {
            background: transparent; color: white; border: 2px solid rgba(255,255,255,0.5);
        }
        .hero-btn-outline:hover { background: rgba(255,255,255,0.12); border-color: white; color: white; }

        /* ── Cards ────────────────────────────────────────────── */
        .feature-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 2rem 1.5rem;
            text-align: center; transition: all 0.35s ease;
            box-shadow: var(--shadow-sm);
        }
        .feature-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-xl);
            border-color: transparent;
        }
        .feature-icon {
            width: 64px; height: 64px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 1.25rem;
        }
        .feature-icon.blue  { background: rgba(26,86,219,0.1);  color: var(--primary); }
        .feature-icon.green { background: rgba(16,185,129,0.1);  color: var(--success); }
        .feature-icon.amber { background: rgba(245,158,11,0.1);  color: var(--warning); }
        .feature-card h5 { font-weight: 700; margin-bottom: 0.6rem; }
        .feature-card p  { color: var(--text-secondary); font-size: 0.92rem; margin-bottom: 0; }

        /* ── Auth Cards ──────────────────────────────────────── */
        .auth-wrapper {
            min-height: calc(100vh - 72px);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);
            width: 100%; max-width: 460px; padding: 2.5rem;
        }
        .auth-card .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-card .auth-header .icon-circle {
            width: 56px; height: 56px; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: inline-flex; align-items: center; justify-content: center;
            color: white; font-size: 1.4rem; margin-bottom: 1rem;
        }
        .auth-card .auth-header h3 { font-weight: 700; }
        .auth-card .auth-header p { color: var(--text-secondary); font-size: 0.9rem; }

        /* ── Form Controls ───────────────────────────────────── */
        .form-floating > .form-control,
        .form-floating > .form-select {
            border: 1.5px solid var(--border); border-radius: 10px;
            padding: 1rem 0.9rem; height: auto;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
        .form-floating > label { color: var(--text-secondary); padding: 1rem 0.9rem; }
        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none; color: white; font-weight: 600;
            padding: 0.75rem 1.5rem; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(26,86,219,0.3);
            transition: all 0.3s ease;
        }
        .btn-primary-gradient:hover {
            box-shadow: 0 4px 16px rgba(26,86,219,0.45);
            transform: translateY(-1px); color: white;
        }

        /* ── Report Form ──────────────────────────────────────── */
        .report-wrapper { padding: 2rem 0 4rem; }
        .report-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);
            padding: 2.5rem;
        }
        .report-card .report-header { margin-bottom: 2rem; }
        .report-card .report-header h3 { font-weight: 700; }
        .report-card .report-header p { color: var(--text-secondary); }

        .image-upload-zone {
            border: 2px dashed var(--border); border-radius: var(--radius);
            padding: 2rem; text-align: center; cursor: pointer;
            transition: all 0.3s ease; background: var(--background);
        }
        .image-upload-zone:hover { border-color: var(--primary); background: rgba(26,86,219,0.03); }
        .image-upload-zone i { font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .image-upload-zone p { color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0; }

        /* ── Footer ───────────────────────────────────────────── */
        .main-footer {
            background: var(--text-primary); color: rgba(255,255,255,0.7);
            padding: 2rem 0; text-align: center; font-size: 0.9rem;
        }
        .main-footer a { color: var(--accent); text-decoration: none; }

        /* ── Alert styling ─────────────────────────────────────── */
        .alert { border-radius: var(--radius); border: none; font-size: 0.9rem; }
        .alert-success { background: rgba(16,185,129,0.1); color: #065f46; }
        .alert-danger  { background: rgba(239,68,68,0.1);  color: #991b1b; }

        /* ── Utility ──────────────────────────────────────────── */
        .text-link { color: var(--primary); text-decoration: none; font-weight: 500; }
        .text-link:hover { text-decoration: underline; }
        .divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; color: var(--text-secondary); font-size: 0.85rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* ── Animations ───────────────────────────────────────── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeInUp 0.5s ease forwards; }
        .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
    </style>
    @yield('styles')
</head>
<body>

    <!-- ─── Navbar ──────────────────────────────────────────── -->
    <nav class="main-navbar navbar navbar-expand-lg" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <span class="navbar-brand-icon"><i class="fas fa-search-location"></i></span>
                Lost & Found
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                    <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('report/lost') ? 'active' : '' }}" href="{{ url('/report/lost') }}"><i class="fas fa-exclamation-triangle me-1"></i> Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('report/found') ? 'active' : '' }}" href="{{ url('/report/found') }}"><i class="fas fa-hand-holding-heart me-1"></i> Report Found</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('search') ? 'active' : '' }}" href="{{ url('/search') }}"><i class="fas fa-search me-1"></i> Search</a></li>
                    
                    @if(session()->has('auth_user_id'))
                        <li class="nav-item"><a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                        @if(session('auth_user_role') === 'admin')
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" href="{{ url('/admin') }}"><i class="fas fa-user-shield me-1"></i> Admin</a></li>
                        @elseif(session('auth_user_role') === 'staff')
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/claims') ? 'active' : '' }}" href="{{ url('/admin/claims') }}"><i class="fas fa-clipboard-check me-1"></i> Claims</a></li>
                        @endif
                        <li class="nav-item ms-lg-2">
                            <form method="POST" action="{{ url('/logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-nav-outline py-1 px-3" style="font-size:0.9rem;">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item ms-lg-2"><a class="btn btn-nav-outline" href="{{ url('/login') }}">Login</a></li>
                        <li class="nav-item ms-lg-1"><a class="btn btn-nav-solid" href="{{ url('/register') }}">Register</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- ─── Flash Messages ──────────────────────────────────── -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- ─── Content ─────────────────────────────────────────── -->
    @yield('content')

    <!-- ─── Footer ──────────────────────────────────────────── -->
    <footer class="main-footer">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Lost and Found Tracking System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 10);
        });
    </script>
    @yield('scripts')
</body>
</html>

<?php



?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIG Batik Sumenep - Dashboard</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons & Fonts --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-o9N1jU5m6c0tqkH+g1YnhhZPmI4n8WwHP7rY0w2bY+E="
        crossorigin="" />

    {{-- Custom Style --}}
    <style>
        nav.navbar {
            transition: all 0.4s ease;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.5s ease;
            /* Slow motion effect */
            background: linear-gradient(90deg, #007bff, #6f42c1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-scrolled {
            transform: translateY(-3px);
            opacity: 0.95;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }


        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #0d6efd, #6610f2);
        }

        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
        }

        .nav-link {
            color: #f8f9fa !important;
            margin-right: 1rem;
            transition: 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #ffc107 !important;
        }

        .logout-btn {
            color: #fff !important;
            font-weight: 500;
        }

        footer {
            margin-top: 3rem;
            padding: 1rem 0;
            text-align: center;
            background-color: #fff;
            border-top: 1px solid #ddd;
            color: #777;
        }

        .dashboard-title {
            margin: 1.5rem 0;
        }

        .card {
            border-radius: 12px;
            transition: all 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        footer {
            background-color: #fff;
            border-top: 1px solid #ddd;
            color: #777;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
            position: sticky;
            bottom: 0;
            width: 100%;
        }

        .alert {
            animation: fadeDown 0.6s ease;
        }
    </style>
</head>

<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="bi bi-flower1 me-1"></i> SIG Batik Sumenep
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">

                    {{-- Jika user login --}}
                    @auth
                    {{-- ========== DASHBOARD ========== --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                            class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    {{-- ========== MENU ADMIN ========== --}}
                    @if(auth()->user()->role_id === 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('dashboard/partners*') || request()->is('dashboard/users*') ? 'active' : '' }}"
                            href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Kelola Data
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminMenu">
                            <li>
                                <a class="dropdown-item {{ request()->is('dashboard/partners*') ? 'active' : '' }}"
                                    href="{{ route('dashboard.partners.index') }}">
                                    <i class="bi bi-building"></i> Data UMKM
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->is('dashboard/users*') ? 'active' : '' }}"
                                    href="{{ route('login') }}">
                                    <i class="bi bi-people"></i> Data Users
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- ========== MENU PARTNER ========== --}}
                    @if(auth()->user()->role_id === 2)
                    <li class="nav-item">
                        <a href="{{ route('dashboard.partners.index') }}"
                            class="nav-link {{ request()->is('dashboard/partners*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i> Data UMKM Saya
                        </a>
                    </li>
                    @endif

                    {{-- ========== DROPDOWN USER INFO ========== --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset(auth()->user()->image ?? 'images/default-user.png') }}"
                                alt="avatar" class="rounded-circle me-2" width="30" height="30">
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li class="dropdown-header text-center">
                                <strong>{{ auth()->user()->full_name ?? auth()->user()->name }}</strong>
                                <br>
                                <span class="badge bg-info text-dark mt-1">
                                    {{ auth()->user()->role_id == 1 ? 'Administrator' : 'Partner' }}
                                </span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a href="{{ route('login') }}" class="dropdown-item">
                                    <i class="bi bi-person"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    {{-- ========== TAMU BELUM LOGIN ========== --}}
                    <li class="nav-item">
                        <a href="{{ route('login') }}"
                            class="nav-link {{ request()->is('login') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}"
                            class="nav-link {{ request()->is('register') ? 'active' : '' }}">
                            <i class="bi bi-person-plus"></i> Daftar
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>



    {{-- Main content --}}
    <main class="container my-4 flex-fill py-4">
        @yield('content')
    </main>

    <footer>
        <small>&copy; {{ date('Y') }} SIG Batik Sumenep. All Rights Reserved.</small>
    </footer>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @yield('scripts')
</body>

</html>

<!-- Scrypt Leaflet -->
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-o9YSmkF2pYRsj6R+wOB1p5MNDoAuCEi0aYawslmYd2M="
    crossorigin="">
</script>

<!-- Scrypt Navbar -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const navbar = document.querySelector("nav.navbar");
        let lastScroll = 0;
        let ticking = false;

        const handleScroll = () => {
            const currentScroll = window.scrollY;

            // Selalu tampil, tapi beri efek animasi halus
            if (currentScroll > lastScroll) {
                // Scroll ke bawah → ubah opacity sedikit
                navbar.classList.add("navbar-scrolled");
            } else {
                // Scroll ke atas → tetap tampil (smooth)
                navbar.classList.remove("navbar-scrolled");
            }

            lastScroll = currentScroll;
            ticking = false;
        };

        window.addEventListener("scroll", () => {
            if (!ticking) {
                window.requestAnimationFrame(handleScroll);
                ticking = true;
            }
        });
    });

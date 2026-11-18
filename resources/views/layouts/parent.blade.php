<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'بوابة ولي الأمر') | Smart School</title>

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 & FontAwesome 6.5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #06b6d4 100%);
            --header-gradient: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e3a8a 100%);
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active-gradient: linear-gradient(90deg, rgba(79, 70, 229, 0.25) 0%, rgba(79, 70, 229, 0.05) 100%);
            --sidebar-active-border: #6366f1;
            --surface-bg: #f8fafc;
            --card-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            --card-shadow-hover: 0 20px 30px -10px rgba(15, 23, 42, 0.12), 0 10px 15px -5px rgba(15, 23, 42, 0.06);
            --radius-lg: 1.25rem;
            --radius-md: 0.875rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--surface-bg);
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* 📱 Sidebar Styling */
        .parent-sidebar {
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: #f8fafc;
            position: fixed;
            top: 0;
            right: 0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
        }

        .sidebar-brand-box {
            padding: 1.5rem 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .brand-badge {
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.85rem;
        }

        .sidebar-nav-container::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .nav-section-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 0.75rem 0.85rem 0.35rem;
        }

        .parent-sidebar .nav-link-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 0.35rem;
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .parent-sidebar .nav-link-custom:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
            transform: translateX(-3px);
        }

        .parent-sidebar .nav-link-custom.active {
            color: #ffffff;
            background: var(--sidebar-active-gradient);
            border-color: rgba(99, 102, 241, 0.4);
            border-right: 4px solid var(--sidebar-active-border);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);
        }

        .parent-sidebar .nav-link-custom i {
            width: 22px;
            font-size: 1.05rem;
            margin-left: 0.75rem;
            color: #64748b;
            transition: all 0.2s ease;
            text-align: center;
        }

        .parent-sidebar .nav-link-custom:hover i,
        .parent-sidebar .nav-link-custom.active i {
            color: #818cf8;
        }

        /* 📱 Top Header & Main Content */
        .parent-main-content {
            margin-right: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .parent-topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.85rem 1.75rem;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .user-avatar-pill {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.35rem 0.85rem 0.35rem 0.5rem;
            background: #f1f5f9;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .user-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }

        .page-content-wrapper {
            flex: 1;
            padding: 1.75rem;
        }

        /* 💎 Common Modern Elements */
        .page-header-card {
            background: var(--header-gradient);
            border-radius: var(--radius-lg);
            color: white;
            padding: 2rem 2.25rem;
            border: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(30, 27, 75, 0.25);
        }

        .page-header-card::before {
            content: '';
            position: absolute;
            top: -40%;
            left: -20%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, rgba(99, 102, 241, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .page-header-card::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: 10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.25) 0%, rgba(6, 182, 212, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .glass-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .stat-badge-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 0.75rem;
        }

        .badge-soft-primary { background: #e0e7ff; color: #4338ca; }
        .badge-soft-success { background: #dcfce7; color: #15803d; }
        .badge-soft-warning { background: #fef3c7; color: #b45309; }
        .badge-soft-danger  { background: #fee2e2; color: #b91c1c; }
        .badge-soft-info    { background: #e0f2fe; color: #0369a1; }

        /* Custom buttons */
        .btn-premium {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 0.65rem 1.4rem;
            border-radius: 50px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            transition: all 0.25s ease;
        }

        .btn-premium:hover {
            background: linear-gradient(135deg, #4338ca, #2563eb);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }

        /* Mobile overlay & burger button */
        .sidebar-toggle-btn {
            display: none;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            color: #334155;
            font-size: 1.1rem;
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1040;
        }

        @media (max-width: 991.98px) {
            .parent-sidebar {
                transform: translateX(100%);
            }
            .parent-sidebar.show {
                transform: translateX(0);
            }
            .parent-main-content {
                margin-right: 0 !important;
            }
            .sidebar-toggle-btn {
                display: inline-block;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- 🌟 Premium Parent Sidebar -->
    <aside class="parent-sidebar shadow-lg" id="parentSidebar">
        <div class="sidebar-brand-box d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-badge text-white">
                    <i class="fas fa-graduation-cap fa-lg"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-white tracking-wide">Smart School</h5>
                    <small class="text-indigo-300" style="color: #a5b4fc; font-size: 0.78rem;">بوابة ولي الأمر الذكية</small>
                </div>
            </div>
            <button class="btn btn-sm btn-link text-muted d-lg-none" id="sidebarCloseBtn">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <div class="sidebar-nav-container">
            <div class="nav-section-title">الرئيسية والمتابعة</div>
            <a href="{{ route('parent.dashboard') }}" class="nav-link-custom {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                <div><i class="fas fa-chart-pie"></i> لوحة المتابعة الشاملة</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.children') }}" class="nav-link-custom {{ request()->routeIs('parent.children') || request()->routeIs('parent.reports.*') ? 'active' : '' }}">
                <div><i class="fas fa-user-graduate"></i> بيانات الأبناء والشهادات</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.attendance') }}" class="nav-link-custom {{ request()->routeIs('parent.attendance') ? 'active' : '' }}">
                <div><i class="fas fa-calendar-check"></i> سجل المواظبة والحضور</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.grades') }}" class="nav-link-custom {{ request()->routeIs('parent.grades') ? 'active' : '' }}">
                <div><i class="fas fa-award"></i> كشف الدرجات الأكاديمية</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.exams') }}" class="nav-link-custom {{ request()->routeIs('parent.exams') ? 'active' : '' }}">
                <div><i class="fas fa-calendar-alt"></i> جدول الامتحانات</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <div class="nav-section-title mt-2">الخدمات والأنشطة</div>
            <a href="{{ route('parent.wallet') }}" class="nav-link-custom {{ request()->routeIs('parent.wallet') ? 'active' : '' }}">
                <div><i class="fas fa-wallet text-warning"></i> محفظة المقصف الذكي</div>
                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 0.7rem;">فوري</span>
            </a>

            <a href="{{ route('parent.fees') }}" class="nav-link-custom {{ request()->routeIs('parent.fees') ? 'active' : '' }}">
                <div><i class="fas fa-file-invoice-dollar"></i> الرسوم المدرسية والأقساط</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.homeworks') }}" class="nav-link-custom {{ request()->routeIs('parent.homeworks') ? 'active' : '' }}">
                <div><i class="fas fa-book-reader"></i> الواجبات المنزلية</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.leaves') }}" class="nav-link-custom {{ request()->routeIs('parent.leaves') ? 'active' : '' }}">
                <div><i class="fas fa-door-open"></i> طلبات الاستئذان والغياب</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <a href="{{ route('parent.behaviour') }}" class="nav-link-custom {{ request()->routeIs('parent.behaviour') ? 'active' : '' }}">
                <div><i class="fas fa-shield-halved"></i> سجل السلوك والانضباط</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>

            <div class="nav-section-title mt-2">التواصل والإعدادات</div>
            <a href="{{ route('messages.index') }}" class="nav-link-custom {{ request()->routeIs('messages.*') || request()->routeIs('parent.messages') ? 'active' : '' }}">
                <div><i class="fas fa-comments text-info"></i> المحادثات والرسائل</div>
                @if(isset($unreadCount) && $unreadCount > 0)
                    <span class="badge bg-danger rounded-pill px-2">{{ $unreadCount }}</span>
                @endif
            </a>

            <a href="{{ route('parent.profile') }}" class="nav-link-custom {{ request()->routeIs('parent.profile') || request()->routeIs('profile.edit') ? 'active' : '' }}">
                <div><i class="fas fa-gear"></i> إعدادات الحساب الشخصي</div>
                <i class="fas fa-angle-left opacity-50" style="margin:0; font-size:0.75rem;"></i>
            </a>
        </div>

        {{-- أسفل القائمة: تسجيل الخروج --}}
        <div class="p-3 border-top border-secondary border-opacity-25 bg-black bg-opacity-20">
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold" style="font-size: 0.9rem;">
                    <i class="fas fa-arrow-right-from-bracket"></i> تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    <!-- 🖥️ Main View Container -->
    <div class="parent-main-content">
        
        <!-- Top Navbar -->
        <header class="parent-topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn" id="sidebarOpenBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="text-muted small">العام الدراسي 2026 - 2027</span>
                    <h6 class="m-0 fw-bold text-dark">بوابة أولياء الأمور المتكاملة</h6>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('messages.index') }}" class="btn btn-light rounded-circle p-2 position-relative shadow-sm" style="width: 40px; height: 40px;" title="المحادثات">
                    <i class="fas fa-bell text-secondary"></i>
                    @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    @endif
                </a>

                <div class="dropdown">
                    <div class="user-avatar-pill cursor-pointer" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar-circle">
                            {{ mb_substr(Auth::user()->name ?? 'و', 0, 1) }}
                        </div>
                        <div class="text-start d-none d-md-block pe-1">
                            <span class="d-block fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">{{ Auth::user()->name }}</span>
                            <small class="text-muted" style="font-size: 0.75rem;">ولي أمر</small>
                        </div>
                        <i class="fas fa-chevron-down text-muted small ms-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2">
                        <li><a class="dropdown-item rounded-3 py-2" href="{{ route('parent.profile') }}"><i class="fas fa-user-edit text-primary me-2"></i> الملف الشخصي</a></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="{{ route('parent.children') }}"><i class="fas fa-users text-success me-2"></i> بيانات الأبناء</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item rounded-3 py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Emergency Announcement Alert if any -->
        <main class="page-content-wrapper">
            @php
                $parentEmergencyAlert = \App\Models\SchoolAnnouncement::where('is_active', true)
                    ->where('priority', 'emergency')
                    ->whereIn('target_role', ['all', 'parent'])
                    ->latest()
                    ->first();
            @endphp
            @if($parentEmergencyAlert)
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center justify-content-between p-3 mb-4 rounded-4" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-danger text-white rounded-circle shadow-sm">
                        <i class="fas fa-bell fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">🚨 نداء عاجل صادر من إدارة المدرسة: {{ $parentEmergencyAlert->title }}</h6>
                        <p class="mb-0 text-dark small">{{ $parentEmergencyAlert->content }}</p>
                    </div>
                </div>
                <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">تنبيه طوارئ</span>
            </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                        <span class="fw-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle fa-lg text-danger"></i>
                        <span class="fw-semibold">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('parentSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const openBtn = document.getElementById('sidebarOpenBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');

        if(openBtn) {
            openBtn.addEventListener('click', () => {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
            });
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }

        if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if(backdrop) backdrop.addEventListener('click', closeSidebar);
    </script>
    @yield('scripts')
</body>
</html>
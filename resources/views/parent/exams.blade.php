@extends('layouts.parent')
@section('title', 'جدول الامتحانات')

@section('content')

{{-- 1. الترويسة الرئيسية --}}
<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-calendar-alt text-warning me-1"></i> التقويم الدراسي والامتحانات
            </div>
            <h3 class="fw-bold mb-1 text-white">جدول امتحانات الأبناء المعتمد 📅</h3>
            <p class="mb-0 text-white-50">متابعة دقيقة لمواعيد الاختبارات الشهرية والنهائية المقررة لكافة الأبناء.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-print text-primary"></i> طباعة الجدول
            </button>
            <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-light rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-right me-2"></i> عودة
            </a>
        </div>
    </div>
</div>

{{-- 2. شبكة أيام التقويم بتصميم عصري --}}
<div class="row g-3">
    @foreach($calendarDays as $day)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
            
            @if($day['has_exam'])
                {{-- تصميم اليوم الذي يحتوي على امتحان --}}
                <div class="card glass-card h-100 border-0 shadow-sm border-top border-4 border-danger position-relative overflow-hidden" style="border-radius: var(--radius-md);">
                    <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 small rounded-bottom-start" style="font-size: 0.65rem; font-weight: 800;">
                        امتحان ⚠️
                    </div>
                    <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
                        <div>
                            <span class="text-danger fw-bold small d-block mb-1">{{ $day['day_name'] }}</span>
                            <h2 class="fw-bold text-dark mb-0 font-monospace">{{ $day['day_num'] }}</h2>
                            <small class="text-muted d-block mb-3">{{ $day['month_name'] }}</small>
                        </div>
                        
                        <div class="d-flex flex-column gap-1">
                            @foreach($day['exams'] as $exam)
                                <div class="p-2 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-start">
                                    <div class="d-flex align-items-center gap-1 text-danger fw-bold small">
                                        <i class="fas fa-book-open"></i>
                                        <span>{{ $exam->subject->name }}</span>
                                    </div>
                                    <small class="d-block text-muted text-truncate mt-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-user-graduate me-1"></i> {{ $exam->child_name }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                {{-- تصميم الأيام العادية --}}
                <div class="card glass-card h-100 border-0 bg-light bg-opacity-50 text-center" style="border-radius: var(--radius-md); opacity: 0.75;">
                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                        <span class="text-secondary small fw-semibold d-block mb-1">{{ $day['day_name'] }}</span>
                        <h3 class="fw-bold text-muted mb-0 font-monospace">{{ $day['day_num'] }}</h3>
                        <small class="text-muted d-block">{{ $day['month_name'] }}</small>
                    </div>
                </div>
            @endif

        </div>
    @endforeach
</div>

@endsection
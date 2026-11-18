@extends('layouts.parent')
@section('title', 'الواجبات المدرسية')

@section('content')

<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-book-reader text-warning me-1"></i> المهام المدرسية والأنشطة
            </div>
            <h3 class="fw-bold mb-1 text-white">متابعة الواجبات المدرسية اليومية 📚</h3>
            <p class="mb-0 text-white-50">الاطلاع على التكليفات المنزلية والمشاريع المحددة من قبل المعلمين ومواعيد تسليمها النهائية.</p>
        </div>
        <div class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-20 px-3 py-2 rounded-pill">
            إجمالي المهام المعروضة: {{ $homeworks->count() }}
        </div>
    </div>
</div>

<div class="card glass-card border-0 overflow-hidden">
    <div class="p-4 border-bottom bg-light">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list-check text-primary me-2"></i> قائمة الواجبات والتكليفات المنزلية</h5>
    </div>

    <div class="card-body p-4">
        @if($homeworks->count() > 0)
        <div class="table-responsive rounded-4 border overflow-hidden">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small fw-bold">
                    <tr>
                        <th class="py-3 ps-4">المادة الدراسية</th>
                        <th>تفاصيل التكليف والواجب</th>
                        <th class="text-center">الموعد النهائي للتسليم</th>
                        <th>المعلم المكلف</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($homeworks as $hw)
                    @php
                        $isPast = \Carbon\Carbon::parse($hw->due_date)->isPast();
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-indigo-soft text-primary px-3 py-2 rounded-pill fw-bold" style="background:#e0e7ff; color:#4338ca;">
                                <i class="fas fa-book me-1"></i> {{ $hw->subject->name ?? 'مادة عامة' }}
                            </span>
                        </td>
                        <td style="max-width: 320px;">
                            <strong class="text-dark d-block mb-1">{{ $hw->title }}</strong>
                            <small class="text-muted">{{ $hw->description }}</small>
                        </td>
                        <td class="text-center">
                            <strong class="text-dark font-monospace d-block">{{ \Carbon\Carbon::parse($hw->due_date)->format('Y-m-d') }}</strong>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($hw->due_date)->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="stat-badge-icon badge-soft-info mb-0" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <span class="fw-semibold text-dark small">{{ $hw->teacher->name ?? 'غير محدد' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($isPast)
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-calendar-xmark me-1"></i> موعد منتهي
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-clock me-1"></i> متاح للإنجاز
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 bg-white rounded-4 border">
            <i class="fas fa-clipboard-check fa-4x text-success opacity-25 mb-3"></i>
            <h5 class="fw-bold text-dark mb-1">لا توجد واجبات متراكمة حالياً</h5>
            <p class="text-muted small mb-0">جميع الأبناء منتهون من واجباتهم أو لم يقم المعلمون برفع مهام جديدة اليوم.</p>
        </div>
        @endif
    </div>
</div>

@endsection

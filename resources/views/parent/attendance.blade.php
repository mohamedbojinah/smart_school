@extends('layouts.parent')
@section('title', 'سجل الحضور والمواظبة')

@section('content')

<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-calendar-check text-warning me-1"></i> الانضباط والالتزام المدرسي
            </div>
            <h3 class="fw-bold mb-1 text-white">سجل الحضور والغياب اليومي 📅</h3>
            <p class="mb-0 text-white-50">متابعة دقيقة وفورية لحضور أبنائك اليومي ورصد حالات الغياب والتأخير.</p>
        </div>
        <div>
            <a href="{{ route('parent.leaves') }}" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-envelope-open-text"></i> تقديم طلب استئذان
            </a>
        </div>
    </div>
</div>

@if($children->count() > 0)
    <div class="glass-card overflow-hidden">
        {{-- شريط اختيار الابن --}}
        <div class="p-3 bg-light border-bottom">
            <ul class="nav nav-pills gap-2" id="attendanceTabs" role="tablist">
                @foreach($children as $index => $child)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-bold {{ $index == 0 ? 'active' : '' }}" 
                                id="tab-{{ $child->id }}" 
                                data-bs-toggle="pill" 
                                data-bs-target="#content-{{ $child->id }}" 
                                type="button" role="tab">
                            <i class="fas fa-user-graduate me-2"></i> {{ $child->user->name }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="card-body p-4">
            <div class="tab-content" id="attendanceTabsContent">
                @foreach($children as $index => $child)
                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="content-{{ $child->id }}" role="tabpanel">
                        
                        @php
                            $records = $attendanceData[$child->id] ?? collect();
                            $presentCount = $records->where('status', 1)->count();
                            $absentCount = $records->where('status', 0)->count();
                            $totalDays = $records->count();
                            $attendanceRate = $totalDays > 0 ? round(($presentCount / $totalDays) * 100) : 100;
                        @endphp

                        {{-- مؤشرات الحضور المصغرة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 text-center">
                                    <div class="text-success fs-3 fw-bold">{{ $presentCount }}</div>
                                    <small class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i> أيام الحضور</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-center">
                                    <div class="text-danger fs-3 fw-bold">{{ $absentCount }}</div>
                                    <small class="text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i> أيام الغياب</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 text-center">
                                    <div class="text-primary fs-3 fw-bold">{{ $totalDays }}</div>
                                    <small class="text-primary fw-semibold"><i class="fas fa-calendar-days me-1"></i> إجمالي الأيام</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-4 bg-indigo-soft border border-indigo border-opacity-25 text-center" style="background:#e0e7ff;">
                                    <div class="fs-3 fw-bold" style="color:#4338ca;">{{ $attendanceRate }}%</div>
                                    <small class="fw-semibold" style="color:#4338ca;"><i class="fas fa-chart-pie me-1"></i> نسبة الالتزام</small>
                                </div>
                            </div>
                        </div>

                        {{-- جدول السجل اليومي --}}
                        @if($records->count() > 0)
                            <div class="table-responsive rounded-4 border overflow-hidden">
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead class="table-light text-secondary small fw-bold">
                                        <tr>
                                            <th class="py-3">تاريخ اليوم</th>
                                            <th>اليوم</th>
                                            <th>الحالة المسجلة</th>
                                            <th>ملاحظات المدرسة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                        <tr>
                                            <td class="fw-bold text-dark font-monospace">{{ $record->attendance_date }}</td>
                                            <td class="text-secondary">{{ \Carbon\Carbon::parse($record->attendance_date)->locale('ar')->translatedFormat('l') }}</td>
                                            <td>
                                                @if($record->status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fas fa-check-circle me-1"></i> حاضر
                                                    </span>
                                                @elseif($record->status == 2)
                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fas fa-clock me-1"></i> متأخر
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fas fa-times-circle me-1"></i> غائب
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $record->note ?? 'لا توجد ملاحظات' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 bg-white rounded-4 border">
                                <i class="fas fa-calendar-check fa-3x text-muted opacity-25 mb-3"></i>
                                <h6 class="fw-bold text-dark">لا يوجد سجل حضور وغياب مرصود</h6>
                                <p class="text-muted small mb-0">لم يتم إدخال كشوفات الحضور لهذا الفصل بعد.</p>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="glass-card text-center py-5">
        <i class="fas fa-calendar-times fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="fw-bold text-dark">لا يوجد أبناء مرتبطين بهذا الحساب</h5>
    </div>
@endif

@endsection
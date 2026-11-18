@extends('layouts.admin')

@section('title', 'نظام الإنذار المبكر الذكي للتعثر الأكاديمي')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fas fa-brain-circuit text-danger me-2"></i> نظام الإنذار المبكر الذكي (AI Early Warning)
            </h3>
            <p class="text-muted small mb-0">تحليل استباقي مدعوم بالذكاء الاصطناعي لكشف الطلاب المعرضين للتعثر الدراسي أو الغياب المزمن قبل الاختبارات النهائية</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-3 fw-bold">
                <i class="fas fa-print me-1"></i> طباعة تقرير التدخل
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                لوحة التحكم
            </a>
        </div>
    </div>

    <!-- AI KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-danger-subtle border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-danger fw-bold d-block mb-1">حالات الخطر الحرج (عاجل)</small>
                        <h2 class="fw-bold text-danger mb-0">{{ $criticalCount ?? 0 }}</h2>
                        <small class="text-muted" style="font-size: 0.75rem;">معدل أقل من 50% أو غياب متكرر</small>
                    </div>
                    <div class="p-3 bg-white rounded-circle text-danger shadow-sm">
                        <i class="fas fa-triangle-exclamation fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning-subtle border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-warning-emphasis fw-bold d-block mb-1">حالات بحاجة لمتابعة</small>
                        <h2 class="fw-bold text-dark mb-0">{{ $moderateCount ?? 0 }}</h2>
                        <small class="text-muted" style="font-size: 0.75rem;">تراجع ملحوظ في الأداء الشهري</small>
                    </div>
                    <div class="p-3 bg-white rounded-circle text-warning shadow-sm">
                        <i class="fas fa-chart-line-down fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary-subtle border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-primary fw-bold d-block mb-1">إجمالي الطلاب تحت الرصد</small>
                        <h2 class="fw-bold text-primary mb-0">{{ count($atRiskStudents) }}</h2>
                        <small class="text-muted" style="font-size: 0.75rem;">مؤشرات تقييم ذكية استباقية</small>
                    </div>
                    <div class="p-3 bg-white rounded-circle text-primary shadow-sm">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- At-Risk Students Grid -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fas fa-users-viewfinder me-2 text-danger"></i> قائمة الطلاب المعرضين للتعثر وتوصيات التدخل</span>
            <span class="badge bg-dark rounded-pill">{{ count($atRiskStudents) }} طالب</span>
        </div>

        <div class="card-body p-4">
            @if(empty($atRiskStudents))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shield-heart fa-4x text-success mb-3 opacity-75"></i>
                    <h5 class="fw-bold text-dark">المؤشرات ممتازة ومستقرة!</h5>
                    <p class="mb-0 text-muted">لا يوجد أي طالب حالياً يقع ضمن نطاق الخطورة الأكاديمية أو الغياب الحرج في المدرسة.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($atRiskStudents as $student)
                        <div class="col-lg-6">
                            <div class="card border border-2 rounded-4 p-4 h-100 shadow-sm {{ $student['risk_score'] >= 60 ? 'border-danger-subtle bg-danger-subtle bg-opacity-10' : 'border-warning-subtle bg-warning-subtle bg-opacity-10' }}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-white border shadow-sm d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark">{{ $student['student_name'] }}</h5>
                                            <small class="text-muted">{{ $student['class_name'] }} | <span class="font-monospace">STD-{{ $student['student_id'] }}</span></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $student['badge_color'] }} rounded-pill px-3 py-2 fw-bold">
                                        {{ $student['risk_level'] }}
                                    </span>
                                </div>

                                <!-- Risk Score Progress -->
                                <div class="mb-3 bg-white p-3 rounded-3 border">
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span class="text-muted">مؤشر الخطورة الأكاديمي:</span>
                                        <span class="text-{{ $student['badge_color'] }}">{{ $student['risk_score'] }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $student['badge_color'] }}" style="width: {{ $student['risk_score'] }}%;"></div>
                                    </div>
                                    <div class="row text-center mt-2 pt-2 border-top g-2 small">
                                        <div class="col-6">
                                            <span class="text-muted d-block">المعدل العام:</span>
                                            <span class="fw-bold {{ $student['average_score'] < 50 ? 'text-danger' : 'text-dark' }}">{{ $student['average_score'] }}%</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">نسبة الغياب:</span>
                                            <span class="fw-bold {{ $student['absence_rate'] > 15 ? 'text-danger' : 'text-dark' }}">{{ $student['absence_rate'] }}% ({{ $student['absent_days'] }} يوم)</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reasons Identified -->
                                <div class="mb-3">
                                    <small class="fw-bold text-dark d-block mb-1"><i class="fas fa-microscope text-primary me-1"></i> أسباب رصد الحالة:</small>
                                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.6;">
                                        @foreach($student['reasons'] as $r)
                                            <li>{{ $r }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- AI Recommendation -->
                                <div class="mt-auto p-3 rounded-3 bg-white border border-primary-subtle shadow-sm">
                                    <div class="d-flex gap-2">
                                        <i class="fas fa-lightbulb text-warning fa-lg mt-1"></i>
                                        <div>
                                            <span class="fw-bold small text-primary d-block mb-1">خطة التدخل الذكي المقترحة (AI Recommendation):</span>
                                            <p class="mb-0 small text-dark" style="line-height: 1.5;">{{ $student['recommendation'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-3 d-flex gap-2 justify-content-end">
                                    <a href="{{ route('student.badge.show', $student['student_id']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="fas fa-id-card me-1"></i> البطاقة
                                    </a>
                                    <a href="{{ route('messages.chat', $student['student_id']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-comment-dots me-1"></i> مراسلة ولي الأمر
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

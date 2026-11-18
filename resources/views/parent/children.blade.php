@extends('layouts.parent')
@section('title', 'بيانات الأبناء والشهادات')

@section('content')

    <div class="page-header-card mb-4 d-print-none">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                    <i class="fas fa-users-gear text-warning me-1"></i> الإدارة الشاملة للطلاب
                </div>
                <h3 class="fw-bold mb-1 text-white">بيانات الأبناء والتقارير الأكاديمية 🎓</h3>
                <p class="mb-0 text-white-50">الملفات الشخصية، الهوية الرقمية، بورتفوليو الإنجازات، والشهادات المعتمدة رقمياً.</p>
            </div>
            <a href="{{ route('parent.dashboard') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-right me-2 text-primary"></i> العودة للوحة التحكم
            </a>
        </div>
    </div>

    @if(isset($children) && $children->count())
        <div class="row g-4">
            @foreach($children as $child)
                @php
                    $u = $child->user;
                    $studentName = $child->name ?? $u->name ?? 'طالب';
                    $className = $child->class_name ?? $child->schoolClass->name ?? 'غير محدد';
                    $sectionName = $child->section ?? $child->schoolClass->section ?? null;
                    $studentCode = $child->student_code ?? ('STU-' . str_pad($child->user_id ?? $child->id, 4, '0', STR_PAD_LEFT));
                    $nationalId = $child->national_id ?? ($u->phone ?? '2026' . ($child->user_id ?? $child->id) . '098');
                    $birthDate = $child->birth_date ?? ($u->birth_date ?? '2015-05-12');
                    $studentGender = $child->gender ?? 'أنثى';
                    $studentId = $child->user_id ?? $child->id;
                @endphp
                <div class="col-lg-6">
                    <div class="card glass-card h-100 overflow-hidden border-0">
                        {{-- رأس بطاقة الابن --}}
                        <div class="p-4 border-bottom bg-gradient" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="child-avatar-box">
                                        {{ mb_substr($studentName, 0, 1) }}
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $studentName }}</h5>
                                        <span class="badge bg-indigo-soft text-primary border px-3 py-1 rounded-pill small" style="background: #eef2ff; color: #4f46e5;">
                                            <i class="fas fa-chalkboard me-1"></i> الصف: {{ $className }}
                                            @if(!empty($sectionName)) - الشعبة: {{ $sectionName }} @endif
                                        </span>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fas fa-circle-check me-1"></i> طالب نشط
                                </span>
                            </div>
                        </div>

                        {{-- تفاصيل البيانات --}}
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border border-light">
                                        <small class="text-muted d-block mb-1">كود الطالب الأكاديمي</small>
                                        <strong class="text-dark font-monospace fs-6">{{ $studentCode }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border border-light">
                                        <small class="text-muted d-block mb-1">الرقم الوطني / القيد</small>
                                        <strong class="text-dark font-monospace fs-6">{{ $nationalId }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border border-light">
                                        <small class="text-muted d-block mb-1">تاريخ الميلاد</small>
                                        <strong class="text-dark fs-6">{{ $birthDate }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border border-light">
                                        <small class="text-muted d-block mb-1">البريد الإلكتروني المدرسي</small>
                                        <strong class="text-dark fs-6 font-monospace">{{ $u->email ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-secondary mb-3 small text-uppercase tracking-wider">
                                <i class="fas fa-bolt text-warning me-1"></i> الخدمات والتقارير المعتمدة
                            </h6>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('student.badge.show', $studentId) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fas fa-id-card me-1 text-primary"></i> البطاقة الذكية 🪪
                                </a>
                                <a href="{{ route('parent.child.portfolio', $studentId) }}" target="_blank" class="btn btn-warning text-dark btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm">
                                    <i class="fas fa-star me-1"></i> بورتفوليو الإنجاز ⭐
                                </a>
                                <a href="{{ route('parent.reports.certificate', $studentId) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                    <i class="fas fa-certificate me-1"></i> الشهادة التفصيلية
                                </a>
                                <a href="{{ route('parent.reports.progress', $studentId) }}" class="btn btn-info text-white btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                    <i class="fas fa-chart-line me-1"></i> المنحنى الأكاديمي
                                </a>
                                <a href="{{ route('parent.reports.discipline', $studentId) }}" class="btn btn-danger btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                    <i class="fas fa-clipboard-check me-1"></i> تقرير الانضباط
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="glass-card text-center py-5">
            <i class="fas fa-users-slash fa-4x text-muted opacity-25 mb-3"></i>
            <h5 class="fw-bold text-dark">لا يوجد أبناء مسجلون على هذا الحساب حالياً</h5>
            <p class="text-muted small">يرجى مراجعة إدارة المدرسة لربط الأبناء برقم الهاتف أو البريد المسجل.</p>
        </div>
    @endif

@endsection

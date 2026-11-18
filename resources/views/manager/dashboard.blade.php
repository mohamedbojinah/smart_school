@extends('layouts.manager')

@section('content')

{{-- تنبيه الطوارئ العام إن وجد --}}
@if(isset($emergencyAlert) && $emergencyAlert)
<div class="alert alert-danger shadow-sm border-0 d-flex align-items-center justify-content-between p-3 mb-4 rounded-4 animate__animated animate__pulse animate__infinite">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-danger text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-1 text-danger">🚨 نداء طوارئ صادر من الإدارة: {{ $emergencyAlert->title }}</h5>
            <p class="mb-0 text-dark small">{{ $emergencyAlert->content }}</p>
        </div>
    </div>
    <span class="badge bg-danger px-3 py-2 fs-6">سارٍ حالياً</span>
</div>
@endif

{{-- ترويسة لوحة القيادة التنفيذية --}}
<div class="card page-header-card mb-4 shadow border-0 position-relative overflow-hidden">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-light text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-crown text-warning me-1"></i> الإدارة العليا للمدرسة
            </span>
            <h2 class="fw-bold mb-1 text-white">لوحة القيادة التنفيذية 📊</h2>
            <p class="mb-0 opacity-75 text-white">
                أهلاً بك، المدير <strong>{{ Auth::user()->name }}</strong> 👋 | متابعة حية لمؤشرات الأداء الأكاديمي، المالي، والانضباط المدرسي.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('manager.ai_advisor') }}" class="btn btn-warning fw-bold text-dark shadow-sm">
                    <i class="fas fa-brain me-1"></i> المستشار الذكي
                </a>
                <a href="{{ route('manager.reports.comprehensive') }}" class="btn btn-outline-light fw-bold shadow-sm">
                    <i class="fas fa-print me-1"></i> التقرير الشامل
                </a>
            </div>
        </div>
    </div>
</div>

{{-- بطاقات المؤشرات الحيوية التنفيذية (KPI Cards) --}}
<div class="row g-3 mb-4">
    {{-- 1. إجمالي الطلاب --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">إجمالي الطلاب</span>
                <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="fas fa-user-graduate"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ $stats['students_count'] }}</h3>
            <span class="text-success small mt-2 d-block"><i class="fas fa-check-circle"></i> مسجلين في الفصول</span>
        </div>
    </div>

    {{-- 2. كادر المعلمين --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">كادر المعلمين</span>
                <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="fas fa-chalkboard-teacher"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ $stats['teachers_count'] }}</h3>
            <a href="{{ route('manager.evaluations.index') }}" class="text-decoration-none small text-success mt-2 d-block">
                تقييم الأداء <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    {{-- 3. نسبة حضور اليوم --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 {{ $stats['today_attendance_rate'] >= 90 ? 'border-info' : 'border-warning' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">حضور اليوم</span>
                <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="fas fa-user-check"></i></span>
            </div>
            <h3 class="fw-bold mb-0 {{ $stats['today_attendance_rate'] >= 90 ? 'text-info' : 'text-warning' }}">
                {{ $stats['today_attendance_rate'] }}%
            </h3>
            <span class="text-muted small mt-2 d-block">
                {{ $stats['today_attendance_rate'] >= 90 ? 'معدل انضباط ممتاز 👏' : 'انتباه لمعدل الغياب ⚠️' }}
            </span>
        </div>
    </div>

    {{-- 4. الموقف المالي (صافي التشغيل) --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 {{ $stats['net_balance'] >= 0 ? 'border-primary' : 'border-danger' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">الرصيد التشغيلي</span>
                <span class="badge bg-light text-dark p-2 rounded-circle"><i class="fas fa-wallet"></i></span>
            </div>
            <h4 class="fw-bold mb-0 {{ $stats['net_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                {{ number_format($stats['net_balance'], 0) }} <small class="fs-6">د.أ</small>
            </h4>
            <span class="text-muted small mt-2 d-block">
                تحصيل: {{ number_format($stats['collected_fees'], 0) }}
            </span>
        </div>
    </div>

    {{-- 5. رادار الخطر الأكاديمي --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 {{ $stats['at_risk_count'] > 0 ? 'border-danger' : 'border-secondary' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">رادار الخطر الأكاديمي</span>
                <span class="badge bg-danger-subtle text-danger p-2 rounded-circle"><i class="fas fa-bell"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-danger">{{ $stats['at_risk_count'] }}</h3>
            <a href="{{ route('manager.ai_advisor') }}" class="text-decoration-none small text-danger mt-2 d-block">
                فحص الحالات الحرجة <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    {{-- 6. قرارات تنتظر الاعتماد --}}
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-top border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold">قرارات بانتظار الاعتماد</span>
                <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="fas fa-signature"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending_approvals_count'] }}</h3>
            <a href="{{ route('manager.approvals.index') }}" class="text-decoration-none small text-warning mt-2 d-block">
                مركز الاعتمادات <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>
</div>

{{-- الرسوم البيانية التفاعلية (Chart.js) --}}
<div class="row g-4 mb-4">
    {{-- رسم بياني: نسبة الحضور والغياب الأسبوعية --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-area text-primary me-2"></i> اتجاه الحضور والانضباط (آخر 7 أيام)</h5>
                    <small class="text-muted">مراقبة حية لنسبة حضور الطلاب لتفادي حالات الغياب الجماعي</small>
                </div>
                <span class="badge bg-success-subtle text-success px-3 py-1">مستقر</span>
            </div>
            <div style="position: relative; height: 260px;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    {{-- رسم بياني: توزيع الطلاب حسب المراحل الدراسية --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100 p-4 bg-white">
            <div class="mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-warning me-2"></i> الكثافة الطلابية</h5>
                <small class="text-muted">توزيع أعداد الطلاب عبر المراحل</small>
            </div>
            <div style="position: relative; height: 260px;" class="d-flex align-items-center justify-content-center">
                <canvas id="gradeDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- قسم الإجراءات السريعة ومتابعة القرارات الأخيرة --}}
<div class="row g-4">
    {{-- لوحة العمليات السريعة والقرارات --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-bolt text-warning me-2"></i> مركز العمليات السريعة</h5>
            </div>
            <div class="row g-3">
                <div class="col-sm-6">
                    <a href="{{ route('manager.announcements.index') }}" class="btn btn-outline-danger w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3">
                        <i class="fas fa-bullhorn fa-2x"></i>
                        <div>
                            <span class="fw-bold d-block">إصدار تعميم / طوارئ</span>
                            <small class="text-muted">بث فوري لجميع الأطراف</small>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('manager.letters.create') }}" class="btn btn-outline-info w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3">
                        <i class="fas fa-award fa-2x"></i>
                        <div>
                            <span class="fw-bold d-block">شهادة شكر / لفت نظر</span>
                            <small class="text-muted">موجّه للمعلمين والإداريين</small>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('manager.approvals.index') }}" class="btn btn-outline-success w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <div>
                            <span class="fw-bold d-block">اعتماد النتائج والخطط</span>
                            <small class="text-muted">توقيع رقمي للمدير</small>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('manager.reports.comprehensive') }}" class="btn btn-outline-primary w-100 py-3 text-start d-flex align-items-center gap-3 rounded-3">
                        <i class="fas fa-file-invoice fa-2x"></i>
                        <div>
                            <span class="fw-bold d-block">تقرير المدرسة الشامل</span>
                            <small class="text-muted">جاهز للطباعة والوزارة</small>
                        </div>
                    </a>
                </div>
            </div>

            {{-- آخر التعاميم الصادرة --}}
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-secondary small">آخر التعاميم والبلاغات</span>
                    <a href="{{ route('manager.announcements.index') }}" class="small text-decoration-none">عرض الكل</a>
                </div>
                @forelse($recentAnnouncements as $ann)
                <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $ann->priority === 'emergency' ? 'bg-danger' : ($ann->priority === 'high' ? 'bg-warning text-dark' : 'bg-primary') }} rounded-pill">
                            {{ $ann->priority === 'emergency' ? 'طوارئ' : 'تعميم' }}
                        </span>
                        <span class="fw-bold text-dark small">{{ $ann->title }}</span>
                    </div>
                    <span class="text-muted" style="font-size: 0.75rem;">{{ $ann->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0 text-center py-2">لا توجد تعاميم منشورة حالياً.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- لوحة سجل الرقابة والنشاطات الأخيرة (Live Audit Trail) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-shield-alt text-primary me-2"></i> سجل العمليات والمراقبة (Audit Log)</h5>
                <a href="{{ route('manager.system_logs') }}" class="btn btn-sm btn-outline-secondary">سجل النشاط كاملاً</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentLogs as $log)
                <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-secondary-subtle text-dark me-2">{{ $log->log_name ?? 'النظام' }}</span>
                        <strong class="text-dark small">{{ $log->causer ? $log->causer->name : 'مستخدم نظام' }}</strong>
                        <span class="text-muted small">{{ $log->description }}</span>
                    </div>
                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $log->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                    <p class="mb-0 small">سجل العمليات هادئ ولا توجد حركات مشبوهة.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- سكربت تشغيل الرسوم البيانية التفاعلية --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. رسم بياني للحضور
    const ctxAttendance = document.getElementById('attendanceChart');
    if (ctxAttendance) {
        new Chart(ctxAttendance, {
            type: 'line',
            data: {
                labels: {!! json_encode($attendanceDaysLabels) !!},
                datasets: [{
                    label: 'نسبة الحضور %',
                    data: {!! json_encode($attendanceDaysData) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 70,
                        max: 100,
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 2. رسم بياني لتوزيع المراحل
    const ctxGrade = document.getElementById('gradeDistributionChart');
    if (ctxGrade) {
        new Chart(ctxGrade, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeLabels) !!},
                datasets: [{
                    data: {!! json_encode($gradeStudentsData) !!},
                    backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1'],
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { family: 'Tahoma' } }
                    }
                }
            }
        });
    }
});
</script>

@endsection
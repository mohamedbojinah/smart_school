@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #4b134f, #c94b4b);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-robot me-1"></i> الذكاء الاصطناعي المؤسسي
            </span>
            <h2 class="fw-bold mb-1 text-white">المستشار الذكي لمدير المدرسة 🤖</h2>
            <p class="mb-0 opacity-75 text-white">
                تحليل تنفيذي شامل لأداء مدرسة ({{ $executiveSummary['school_name'] }}) للعام الدراسي {{ $executiveSummary['academic_year'] }}.
            </p>
        </div>
        <div class="text-end text-white d-none d-md-block">
            <span class="small opacity-75 d-block">تاريخ التقرير الذكي</span>
            <strong>{{ $executiveSummary['generated_at'] }}</strong>
        </div>
    </div>
</div>

{{-- بطاقة المؤشر الصحي العام للمدرسة (School Health Score) --}}
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 text-center bg-white h-100">
            <h5 class="fw-bold text-secondary mb-3">مؤشر الكفاءة المدرسية العام</h5>
            <div class="position-relative d-inline-flex align-items-center justify-content-center my-2">
                <div class="rounded-circle d-flex flex-column align-items-center justify-content-center shadow" 
                     style="width: 140px; height: 140px; border: 8px solid {{ $executiveSummary['health_score'] >= 85 ? '#20c997' : ($executiveSummary['health_score'] >= 70 ? '#ffc107' : '#dc3545') }}; background: #fdfdfd;">
                    <span class="display-5 fw-bold text-dark">{{ $executiveSummary['health_score'] }}</span>
                    <small class="text-muted fw-bold">من 100</small>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge {{ $executiveSummary['health_score'] >= 85 ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 fs-6">
                    {{ $executiveSummary['health_score'] >= 85 ? 'أداء مؤسسي ممتاز 🌟' : 'أداء جيد يحتاج تحسينات ⚖️' }}
                </span>
                <p class="text-muted small mt-2 mb-0">تم احتساب المؤشر عبر دمج نسب الحضور، التحصيل الأكاديمي، والملاءة المالية.</p>
            </div>
        </div>
    </div>

    {{-- أبرز نقاط القوة والأولويات --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt text-success me-2"></i> نقاط القوة التنافسية</h5>
            <ul class="list-group list-group-flush mb-4">
                @foreach($executiveSummary['strengths'] as $strength)
                <li class="list-group-item px-0 border-0 d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle text-success fs-5"></i>
                    <span class="text-dark">{{ $strength }}</span>
                </li>
                @endforeach
            </ul>

            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i> أولويات تستدعي تدخل المدير</h5>
            <ul class="list-group list-group-flush mb-0">
                @foreach($executiveSummary['priorities'] as $priority)
                <li class="list-group-item px-0 border-0 d-flex align-items-center gap-2">
                    <i class="fas fa-arrow-circle-left text-danger fs-5"></i>
                    <span class="text-dark">{{ $priority }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

{{-- التوجيهات والتوصيات الإستراتيجية --}}
<div class="card border-0 shadow-sm p-4 bg-white mb-4">
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> التوصيات الإستراتيجية المقترحة للمدير</h5>
    <div class="row g-3">
        @foreach($executiveSummary['recommendations'] as $domain => $text)
        <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light border-start border-4 border-primary h-100">
                <strong class="text-primary d-block mb-1"><i class="fas fa-chevron-left me-1"></i> {{ $domain }}</strong>
                <p class="text-secondary small mb-0">{{ $text }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- رادار الإنذار المبكر للطلاب المتعثرين --}}
<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-danger mb-0"><i class="fas fa-radiation text-danger me-2"></i> رادار الإنذار المبكر للطلاب المعرضين لخطر الرسوب / التسرب</h5>
            <small class="text-muted">نظام رصد ذكي يستند إلى مؤشرات الغياب وتدني الدرجات للتدخل الاستباقي</small>
        </div>
        <span class="badge bg-danger px-3 py-2">الحالات المرصودة: {{ count($atRiskStudents) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>اسم الطالب</th>
                    <th>الصف / الشعبة</th>
                    <th>مستوى الخطر</th>
                    <th>معدل الدرجات</th>
                    <th>نسبة الغياب</th>
                    <th>الأسباب المرصودة</th>
                    <th>التوصية العلاجية</th>
                    <th>إجراء المدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($atRiskStudents as $item)
                <tr>
                    <td class="fw-bold text-dark">{{ $item['student_name'] }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $item['class_name'] }}</span></td>
                    <td>
                        <span class="badge bg-{{ $item['badge_color'] }} px-2 py-1">
                            {{ $item['risk_level'] }} ({{ $item['risk_score'] }}%)
                        </span>
                    </td>
                    <td class="fw-bold {{ $item['average_score'] < 50 ? 'text-danger' : 'text-dark' }}">
                        {{ $item['average_score'] }}%
                    </td>
                    <td class="fw-bold {{ $item['absence_rate'] > 15 ? 'text-danger' : 'text-dark' }}">
                        {{ $item['absence_rate'] }}% ({{ $item['absent_days'] }} أيام)
                    </td>
                    <td>
                        <small class="text-danger d-block">
                            @foreach($item['reasons'] as $r)
                                <div>• {{ $r }}</div>
                            @endforeach
                        </small>
                    </td>
                    <td class="small text-muted" style="max-width: 250px;">{{ $item['recommendation'] }}</td>
                    <td>
                        <a href="{{ route('manager.letters.create', ['teacher' => $item['student_id']]) }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-envelope me-1"></i> إشعار ولي الأمر
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-check-double fa-2x text-success mb-2 d-block"></i>
                        لا توجد حالات حرجة مرصودة في رادار الخطر حالياً، جميع الطلاب في المستويات الآمنة! 🎉
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

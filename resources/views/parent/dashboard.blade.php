@extends('layouts.parent')
@section('title', 'لوحة تحكم ولي الأمر')

@section('content')

{{-- الترويسة الرئيسية الترحيبية بتدرج لوني فخم --}}
<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold" style="font-size: 0.8rem;">
                <i class="fas fa-sparkles text-warning me-1"></i> متابعة الأبناء الأكاديمية والتربوية
            </div>
            <h2 class="fw-bold mb-1 text-white">مرحباً بك، {{ Auth::user()->name }} 👋</h2>
            <p class="mb-0 text-white-50">نظام ذكي متكامل لمتابعة تحصيل أبنائك، سجل الحضور، المصروف والشهادات المعتمدة في شاشة واحدة.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('parent.exams') }}" class="btn btn-light bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-calendar-alt text-primary"></i>
                <span>جدول الامتحانات</span>
            </a>
            <a href="{{ route('parent.wallet') }}" class="btn btn-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-wallet"></i>
                <span>محفظة المقصف</span>
            </a>
        </div>
    </div>
</div>

@if($children->count() > 0)
    {{-- 1. بطاقات اختيار الأبناء الحديثة (Pills Tabs) --}}
    <div class="mb-4 d-print-none">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="fas fa-users text-primary me-2"></i> اختر الابن لعرض تفاصيله:</h5>
            <span class="text-muted small">إجمالي الأبناء المسجلين: <strong class="text-primary">{{ $children->count() }}</strong></span>
        </div>

        <ul class="nav nav-pills row g-3" id="childrenTabs" role="tablist">
            @foreach($children as $index => $child)
                <li class="nav-item col-12 col-md-6 col-lg-4" role="presentation">
                    <button class="nav-link w-100 p-0 bg-transparent border-0 {{ $index === 0 ? 'active' : '' }}" id="tab-btn-{{ $child->id }}" data-bs-toggle="pill" data-bs-target="#tab-content-{{ $child->id }}" type="button" role="tab">
                        <div class="card glass-card child-select-card h-100 p-3 position-relative overflow-hidden text-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="child-avatar-box">
                                    {{ mb_substr($child->user->name ?? 'ط', 0, 1) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="m-0 fw-bold text-dark mb-1">{{ $child->user->name ?? 'غير معروف' }}</h6>
                                    <div class="d-flex align-items-center gap-2 text-muted small">
                                        <span class="badge bg-light text-secondary border">
                                            <i class="fas fa-chalkboard me-1"></i> {{ $child->schoolClass->name ?? 'غير محدد' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="active-indicator">
                                    <i class="fas fa-check-circle text-primary fs-5"></i>
                                </div>
                            </div>
                        </div>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- 2. محتوى تفاصيل الأبناء --}}
    <div class="tab-content" id="childrenTabsContent">
        @foreach($children as $index => $child)
            @php $details = $childrenDetails[$child->user_id]; @endphp
            
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="tab-content-{{ $child->id }}" role="tabpanel">
                
                {{-- شريط الإجراءات السريعة للابن المحدد --}}
                <div class="glass-card p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3 d-print-none">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-bolt text-warning fs-5"></i>
                        <span class="fw-bold text-dark">روابط سريعة للطالب: {{ $child->user->name }}:</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('student.badge.show', $child->user_id ?? $child->id) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm">
                            <i class="fas fa-id-card me-1"></i> بطاقة الطالب 🪪
                        </a>
                        <a href="{{ route('parent.child.portfolio', $child->user_id ?? $child->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill px-3 shadow-sm fw-bold">
                            <i class="fas fa-star me-1"></i> بورتفوليو الإنجازات ⭐
                        </a>
                        <a href="{{ route('parent.reports.certificate', $child->user_id ?? $child->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                            <i class="fas fa-certificate me-1"></i> كشف الدرجات الرسمي 📜
                        </a>
                        <a href="{{ route('parent.reports.progress', $child->user_id ?? $child->id) }}" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm">
                            <i class="fas fa-chart-line me-1"></i> المنحنى الأكاديمي 📈
                        </a>
                    </div>
                </div>

                {{-- المؤشرات الثلاثية الإحصائية الملونة --}}
                <div class="row g-4 d-print-none">
                    {{-- بطاقة 1: النسبة الأكاديمية --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative stat-box-academic">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-1">الأداء الدراسي</span>
                                    <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
                                </div>
                                <h2 class="display-5 fw-bold mb-0">{{ $details->percentage }}<span class="fs-4 opacity-75">%</span></h2>
                                <p class="mb-0 mt-2 small opacity-90">
                                    @if($details->percentage >= 85)
                                        <i class="fas fa-sparkles text-warning me-1"></i> مستوى استثنائي ممتاز
                                    @elseif($details->percentage >= 70)
                                        <i class="fas fa-thumbs-up me-1"></i> مستوى جيد جداً
                                    @elseif($details->percentage >= 50)
                                        <i class="fas fa-check me-1"></i> مستوى متوسط ومقبول
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i> يحتاج لدعم ومتابعة
                                    @endif
                                </p>
                            </div>
                            <div class="stat-bg-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>

                    {{-- بطاقة 2: رصيد المحفظة --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative stat-box-wallet">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-1">المحفظة والمقصف</span>
                                    <i class="fas fa-wallet fa-2x opacity-75"></i>
                                </div>
                                <h2 class="display-5 fw-bold mb-0">{{ number_format($details->wallet ?? 0, 2) }}<span class="fs-4 opacity-75"> د.ل</span></h2>
                                <p class="mb-0 mt-2 small opacity-90 d-flex justify-content-between align-items-center">
                                    <span>الرصيد المتاح لمشتريات المقصف</span>
                                    <a href="{{ route('parent.wallet') }}" class="text-white text-decoration-underline fw-bold small">إدارة السقف</a>
                                </p>
                            </div>
                            <div class="stat-bg-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>

                    {{-- بطاقة 3: إحصائية الحضور والغياب --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative stat-box-attendance">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-1">سجل المواظبة</span>
                                    <i class="fas fa-user-clock fa-2x opacity-75"></i>
                                </div>
                                <div class="d-flex justify-content-around text-center mt-3 pt-1">
                                    <div>
                                        <div class="fs-3 fw-bold">{{ $details->attendance['present'] ?? 0 }}</div>
                                        <small class="opacity-90"><i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> حضور</small>
                                    </div>
                                    <div class="border-start border-white border-opacity-25"></div>
                                    <div>
                                        <div class="fs-3 fw-bold">{{ $details->attendance['late'] ?? 0 }}</div>
                                        <small class="opacity-90"><i class="fas fa-circle text-warning me-1" style="font-size: 8px;"></i> تأخير</small>
                                    </div>
                                    <div class="border-start border-white border-opacity-25"></div>
                                    <div>
                                        <div class="fs-3 fw-bold">{{ $details->attendance['absent'] ?? 0 }}</div>
                                        <small class="opacity-90"><i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i> غياب</small>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-bg-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- قسم الرسوم البيانية التفاعلية الفخمة --}}
                <div class="row g-4 mt-1 d-print-none">
                    {{-- الرسم البياني للأداء الأكاديمي --}}
                    <div class="col-lg-8">
                        <div class="glass-card h-100 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><i class="fas fa-chart-column text-indigo me-2" style="color: #4f46e5;"></i> تحليل الأداء الأكاديمي للمواد</h5>
                                    <span class="text-muted small">مقارنة مستوى إنجاز الطالب في المواد التعليمية المقررة</span>
                                </div>
                                <span class="badge bg-indigo-soft text-primary px-3 py-2 rounded-pill">نسبة 100%</span>
                            </div>
                            <div style="height: 340px; position: relative;">
                                <canvas id="radarChart-{{ $child->id }}"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- الرسم البياني الدائري للحضور --}}
                    <div class="col-lg-4">
                        <div class="glass-card h-100 p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie text-success me-2"></i> توزيع المواظبة</h5>
                                <a href="{{ route('parent.attendance') }}" class="text-primary small text-decoration-none fw-semibold">السجل الكامل</a>
                            </div>
                            
                            <div class="p-2 text-center" style="height: 260px; position: relative;">
                                @if(array_sum($details->attendance) > 0)
                                    <canvas id="doughnutChart-{{ $child->id }}"></canvas>
                                @else
                                    <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                                        <i class="fas fa-calendar-xmark fa-3x text-secondary opacity-25 mb-2"></i>
                                        <p class="mb-0 small">لا يوجد سجل حضور وغياب مرصود حتى الآن.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-light p-3 rounded-4 mt-2">
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span>إجمالي الأيام المرصودة:</span>
                                    <strong class="text-dark">{{ array_sum($details->attendance) }} يوم</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- قائمة التقييمات التفصيلية للمواد (Modern Accordion) --}}
                <div class="mt-5 d-print-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-layer-group text-primary me-2"></i> كشف درجات المواد والتقييمات الجزئية:</h5>
                            <span class="text-muted small">اضغط على أي مادة لعرض تفاصيل الواجبات والامتحانات النصفية والشهرية المرصودة</span>
                        </div>
                        <a href="{{ route('parent.grades') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-external-link-alt me-1"></i> كشف الدرجات الكامل
                        </a>
                    </div>

                    <div class="accordion modern-accordion" id="accordion-{{ $child->id }}">
                        @forelse($details->subjects as $subject)
                            <div class="accordion-item glass-card mb-3 border-0 overflow-hidden">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3 px-4 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $child->id }}-{{ $loop->index }}">
                                        <div class="d-flex align-items-center justify-content-between w-100 pe-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="subject-icon-box">
                                                    <i class="fas fa-book-open"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $subject->subject_name }}</h6>
                                                    <small class="text-muted">المعلم المقرر والتقييمات</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-end">
                                                    <span class="fs-5 fw-bold text-primary">{{ $subject->total_score ?? '-' }}</span>
                                                    <span class="text-muted small"> / {{ $subject->max_total ?? 100 }}</span>
                                                </div>
                                                @php
                                                    $subjRatio = ($subject->max_total > 0 && $subject->total_score !== null) ? round(($subject->total_score / $subject->max_total) * 100) : 0;
                                                @endphp
                                                <span class="badge {{ $subjRatio >= 85 ? 'bg-success' : ($subjRatio >= 50 ? 'bg-primary' : 'bg-danger') }} rounded-pill px-3 py-1">
                                                    {{ $subjRatio }}%
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $child->id }}-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $child->id }}">
                                    <div class="accordion-body bg-light bg-opacity-50 p-4 border-top">
                                        @if(isset($subject->detailed_marks) && count($subject->detailed_marks) > 0)
                                            <div class="table-responsive bg-white rounded-4 shadow-sm border overflow-hidden">
                                                <table class="table table-hover align-middle mb-0 text-center">
                                                    <thead class="table-light text-secondary small fw-bold">
                                                        <tr>
                                                            <th class="text-start ps-4 py-3">التقييم / الاختبار</th>
                                                            <th>الدرجة العظمى</th>
                                                            <th>درجة الطالب</th>
                                                            <th>الفصل الدراسي</th>
                                                            <th>التقييم</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subject->detailed_marks as $mark)
                                                        <tr>
                                                            <td class="fw-bold text-dark text-start ps-4">
                                                                <i class="fas fa-pencil text-muted me-2 small"></i> {{ $mark->title }}
                                                            </td>
                                                            <td class="text-muted font-monospace">{{ $mark->max_score }}</td>
                                                            <td>
                                                                @if($mark->score !== null)
                                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 fs-6 rounded-pill">
                                                                        {{ $mark->score }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill">لم تُرصد</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-secondary small">الفصل {{ $mark->semester ?? 1 }}</td>
                                                            <td>
                                                                @if($mark->score !== null && $mark->max_score > 0)
                                                                    @php $rate = ($mark->score / $mark->max_score) * 100; @endphp
                                                                    @if($rate >= 90)
                                                                        <span class="text-success small fw-bold">ممتاز ⭐</span>
                                                                    @elseif($rate >= 75)
                                                                        <span class="text-primary small fw-bold">جيد جداً</span>
                                                                    @elseif($rate >= 50)
                                                                        <span class="text-secondary small">مقبول</span>
                                                                    @else
                                                                        <span class="text-danger small fw-bold">ضعيف</span>
                                                                    @endif
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4 bg-white rounded-4 border">
                                                <i class="fas fa-inbox text-muted opacity-25 fa-3x mb-2"></i>
                                                <p class="text-muted mb-0 small">لم يتم رصد اختبارات أو واجبات جزئية لهذه المادة حالياً.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 glass-card">
                                <i class="fas fa-book-open fa-3x text-muted opacity-25 mb-3"></i>
                                <h6 class="text-dark fw-bold">لا توجد مواد مسجلة لهذا الطالب</h6>
                                <p class="text-muted small mb-0">يرجى مراجعة إدارة المدرسة للتأكد من ربط الطالب بالمواد الدراسية.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Chart Data Storage Div --}}
                <div id="chartData-{{ $child->id }}" 
                     data-labels="{{ $details->chartLabels }}" 
                     data-data="{{ $details->chartData }}"
                     data-attendance='@json($details->attendance)'>
                </div>

            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5 glass-card">
        <div class="stat-badge-icon bg-light text-secondary mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
            <i class="fas fa-user-graduate"></i>
        </div>
        <h4 class="text-dark fw-bold mb-2">لا يوجد أبناء مرتبطين بحسابك حالياً</h4>
        <p class="text-muted mb-4" style="max-width: 500px; margin: auto;">
            إذا كان أبناؤك مسجلين في المدرسة، يمكنك التواصل مع إدارة شؤون الطلاب لربطهم إلكترونياً بهذا الحساب للاستفادة من كافة الخدمات الذكية.
        </p>
        <a href="{{ route('messages.index') }}" class="btn btn-premium">
            <i class="fas fa-comment-dots me-2"></i> تواصل مع إدارة المدرسة
        </a>
    </div>
@endif

@endsection

@section('styles')
<style>
    /* Styling for Child Select Pills */
    .child-select-card {
        border-radius: var(--radius-md) !important;
        cursor: pointer;
        transition: all 0.25s ease;
        border: 2px solid transparent !important;
    }

    .child-avatar-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
    }

    .active-indicator {
        display: none;
    }

    .nav-link.active .child-select-card {
        border-color: #4f46e5 !important;
        background: #ffffff !important;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.15) !important;
        transform: translateY(-3px);
    }

    .nav-link.active .active-indicator {
        display: block;
    }

    /* Stat Boxes Gradients */
    .stat-box-academic {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
    }

    .stat-box-wallet {
        background: linear-gradient(135deg, #065f46 0%, #047857 60%, #059669 100%);
    }

    .stat-box-attendance {
        background: linear-gradient(135deg, #9a3412 0%, #c2410c 60%, #ea580c 100%);
    }

    .stat-bg-icon {
        position: absolute;
        left: -15px;
        bottom: -20px;
        opacity: 0.12;
        font-size: 8.5rem;
        color: #ffffff;
        pointer-events: none;
    }

    .subject-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .modern-accordion .accordion-button:not(.collapsed) {
        background-color: #f8fafc;
        box-shadow: none;
    }

    .modern-accordion .accordion-button:focus {
        box-shadow: none;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabPanes.forEach(pane => {
            const childId = pane.id.split('-').pop();
            const dataDiv = document.getElementById('chartData-' + childId);
            
            if (dataDiv) {
                // 1. Bar Chart: Academic Performance
                const radarCanvas = document.getElementById('radarChart-' + childId);
                if (radarCanvas) {
                    let labels = [];
                    let data = [];
                    try { labels = JSON.parse(dataDiv.getAttribute('data-labels') || '[]'); } catch(e){}
                    try { data = JSON.parse(dataDiv.getAttribute('data-data') || '[]'); } catch(e){}
                    
                    if (labels.length > 0) {
                        let ctx = radarCanvas.getContext('2d');
                        let gradient = ctx.createLinearGradient(0, 0, 0, 320);
                        gradient.addColorStop(0, '#4f46e5');
                        gradient.addColorStop(1, '#818cf8');

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'مستوى الأداء %',
                                    data: data,
                                    backgroundColor: gradient,
                                    borderRadius: 8,
                                    barPercentage: 0.55
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        grid: { color: 'rgba(226, 232, 240, 0.8)', drawBorder: false },
                                        ticks: { 
                                            font: { family: 'Cairo', size: 12 },
                                            callback: val => val + '%'
                                        }
                                    },
                                    x: {
                                        grid: { display: false, drawBorder: false },
                                        ticks: { font: { family: 'Cairo', size: 12, weight: 'bold' } }
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#1e293b',
                                        titleFont: { family: 'Cairo', size: 13 },
                                        bodyFont: { family: 'Cairo', size: 13, weight: 'bold' },
                                        padding: 12,
                                        cornerRadius: 10,
                                        callbacks: { label: c => 'نسبة الإنجاز: ' + c.parsed.y + '%' }
                                    }
                                },
                                animation: { duration: 1200, easing: 'easeOutQuart' }
                            }
                        });
                    }
                }
                
                // 2. Doughnut Chart: Attendance
                const doughnutCanvas = document.getElementById('doughnutChart-' + childId);
                if (doughnutCanvas) {
                    let attendance = {};
                    try { attendance = JSON.parse(dataDiv.getAttribute('data-attendance') || '{}'); } catch(e){}
                    
                    const present = attendance.present || 0;
                    const late = attendance.late || 0;
                    const absent = attendance.absent || 0;
                    
                    if (present + absent + late > 0) {
                        new Chart(doughnutCanvas.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['حاضر', 'متأخر', 'غائب'],
                                datasets: [{
                                    data: [present, late, absent],
                                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                    borderWidth: 3,
                                    borderColor: '#ffffff',
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '72%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { 
                                            font: { family: 'Cairo', size: 12, weight: '600' },
                                            boxWidth: 14,
                                            padding: 15
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            }
        });
    });
</script>
@endsection
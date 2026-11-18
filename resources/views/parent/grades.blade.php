@extends('layouts.parent')
@section('title', 'كشف درجات الأبناء')

@section('content')

    <div class="page-header-card mb-4 d-print-none">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                    <i class="fas fa-award text-warning me-1"></i> السجل الأكاديمي والتحصيل العلمي
                </div>
                <h3 class="fw-bold mb-1 text-white">كشف درجات الأبناء الرسمي 📊</h3>
                <p class="mb-0 text-white-50">عرض تفصيلي للدرجات المرصودة في كافة المواد عبر الفصول الدراسية المختلفة.</p>
            </div>
            <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-print text-primary"></i> طباعة الكشف
            </button>
        </div>
    </div>

    @if(isset($children) && $children->count())
        @foreach($children as $child)
            <div class="card glass-card mb-4 border-0 overflow-hidden">
                <div class="p-4 border-bottom bg-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-badge-icon badge-soft-primary mb-0" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ $child->name ?? $child->user->name ?? 'طالب' }}</h5>
                            <small class="text-muted"><i class="fas fa-chalkboard me-1"></i> الصف: {{ $child->class_name ?? $child->schoolClass->name ?? 'غير محدد' }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('parent.reports.certificate', $child->user_id ?? $child->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="fas fa-certificate me-1"></i> الشهادة الكاملة
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if(isset($child->grades) && $child->grades->count())
                        <div class="table-responsive rounded-4 border overflow-hidden">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-light text-secondary small fw-bold">
                                <tr>
                                    <th class="py-3 text-start ps-4">المادة الدراسية</th>
                                    <th>الفصل</th>
                                    <th>الدرجة المحرزة</th>
                                    <th>الدرجة العظمى</th>
                                    <th>النسبة المئوية</th>
                                    <th>التقدير الأكاديمي</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($child->grades as $grade)
                                    @php
                                        $percent = $grade->max_score > 0
                                            ? round(($grade->score / $grade->max_score) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-dark text-start ps-4">
                                            <i class="fas fa-book-open text-primary me-2"></i> {{ $grade->subject_name ?? 'غير محدد' }}
                                            @if(!empty($grade->title))
                                                <small class="d-block text-muted fw-normal mt-1"><i class="fas fa-pencil me-1 small"></i> {{ $grade->title }}</small>
                                            @endif
                                        </td>
                                        <td class="text-secondary small">الفصل {{ $grade->term ?? '1' }}</td>
                                        <td class="fw-bold fs-6 font-monospace text-primary">{{ $grade->score }}</td>
                                        <td class="text-muted font-monospace">{{ $grade->max_score }}</td>
                                        <td>
                                            <span class="badge
                                                @if($percent >= 85) bg-success bg-opacity-10 text-success
                                                @elseif($percent >= 65) bg-primary bg-opacity-10 text-primary
                                                @elseif($percent >= 50) bg-warning bg-opacity-10 text-warning
                                                @else bg-danger bg-opacity-10 text-danger
                                                @endif rounded-pill px-3 py-2 fw-bold">
                                                {{ $percent }}%
                                            </span>
                                        </td>
                                        <td>
                                            @if($percent >= 85)
                                                <span class="fw-bold text-success small">ممتاز ⭐</span>
                                            @elseif($percent >= 75)
                                                <span class="fw-bold text-primary small">جيد جداً</span>
                                            @elseif($percent >= 65)
                                                <span class="fw-semibold text-info small">جيد</span>
                                            @elseif($percent >= 50)
                                                <span class="fw-semibold text-secondary small">مقبول</span>
                                            @else
                                                <span class="fw-bold text-danger small">يحتاج دعم</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 border">
                            <i class="fas fa-award fa-3x text-muted opacity-25 mb-3"></i>
                            <h6 class="fw-bold text-dark">لا توجد درجات معتمدة حتى الآن</h6>
                            <p class="text-muted small mb-0">سيتم عرض نتائج ودرجات الطالب بمجرد رصدها واعتمادها من الإدارة.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="glass-card text-center py-5">
            <i class="fas fa-users-slash fa-4x text-muted opacity-25 mb-3"></i>
            <h5 class="fw-bold text-dark">لا يوجد أبناء مرتبطين بحسابك</h5>
        </div>
    @endif

@endsection

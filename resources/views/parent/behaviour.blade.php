@extends('layouts.parent')
@section('title', 'تقارير السلوك والانضباط')

@section('content')

    <div class="page-header-card mb-4 d-print-none">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                    <i class="fas fa-shield-halved text-warning me-1"></i> التوجيه والإرشاد الطلابي
                </div>
                <h3 class="fw-bold mb-1 text-white">تقارير السلوك والانضباط المدرسي 🛡️</h3>
                <p class="mb-0 text-white-50">متابعة السلوك العام للطلاب، الإشادات التربوية، وملاحظات المرشد والمعلمين.</p>
            </div>
            <a href="{{ route('parent.dashboard') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-right me-2 text-primary"></i> العودة للرئيسية
            </a>
        </div>
    </div>

    @if(isset($behaviourReports) && $behaviourReports->count())
        <div class="card glass-card border-0 overflow-hidden">
            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-file-shield text-primary me-2"></i> سجل الملاحظات السلوكية</h5>
                <span class="badge bg-white border text-secondary px-3 py-2 rounded-pill small">إجمالي التقارير: {{ $behaviourReports->count() }}</span>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive rounded-4 border overflow-hidden">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light text-secondary small fw-bold">
                        <tr>
                            <th class="py-3 text-start ps-4">اسم الطالب</th>
                            <th>الصف الدراسي</th>
                            <th>التاريخ</th>
                            <th>تقييم السلوك</th>
                            <th class="text-start ps-3">ملاحظات المرشد / المعلم</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($behaviourReports as $report)
                            <tr>
                                <td class="fw-bold text-dark text-start ps-4">
                                    <i class="fas fa-user-graduate text-primary me-2"></i> {{ $report->child_name ?? '-' }}
                                </td>
                                <td class="text-secondary small">{{ $report->class_name ?? '-' }}</td>
                                <td class="font-monospace text-secondary">{{ $report->date ?? '-' }}</td>
                                <td>
                                    @php
                                        $level = $report->level ?? 'غير محدد';
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold
                                        @if($level === 'ممتاز') bg-success bg-opacity-10 text-success
                                        @elseif($level === 'جيد جدا') bg-primary bg-opacity-10 text-primary
                                        @elseif($level === 'جيد') bg-info bg-opacity-10 text-info
                                        @elseif($level === 'يحتاج متابعة') bg-warning bg-opacity-10 text-warning
                                        @else bg-secondary bg-opacity-10 text-secondary
                                        @endif">
                                        @if($level === 'ممتاز') <i class="fas fa-star text-warning me-1"></i> @endif
                                        {{ $level }}
                                    </span>
                                </td>
                                <td class="text-start ps-3 text-dark small" style="max-width: 300px;">
                                    {{ $report->note ?? 'لا توجد ملاحظات إضافية' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="glass-card text-center py-5">
            <i class="fas fa-smile-beam fa-4x text-success opacity-25 mb-3"></i>
            <h5 class="fw-bold text-dark">سجل الأبناء نظيف ومثالي ✨</h5>
            <p class="text-muted small mb-0">لم يتم تسجيل أي مخالفات أو ملاحظات سلوكية سلبية على أبنائك، بارك الله في جهودكم.</p>
        </div>
    @endif

@endsection

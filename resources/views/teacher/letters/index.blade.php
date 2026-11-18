@extends('layouts.teacher')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-certificate me-1"></i> السجل التقديري
            </span>
            <h2 class="fw-bold mb-1 text-white">الخطابات وشهادات الشكر الموجهة لك 📜</h2>
            <p class="mb-0 opacity-75 text-white">
                جميع المكاتبات والشهادات وخطابات التقدير الرسمية الصادرة من مكتب مدير المدرسة.
            </p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-award fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>رقم الصادر / القيد</th>
                    <th>نوع الخطاب</th>
                    <th>موضوع الخطاب</th>
                    <th>تاريخ الإصدار</th>
                    <th>صادر عن</th>
                    <th class="text-center">معاينة وطباعة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters as $letter)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                            {{ $letter->reference_no }}
                        </span>
                    </td>
                    <td>
                        @if($letter->type === 'appreciation')
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                <i class="fas fa-award me-1"></i> شهادة شكر وتقدير
                            </span>
                        @elseif($letter->type === 'warning')
                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                <i class="fas fa-exclamation-triangle me-1"></i> لفت نظر رسمي
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                <i class="fas fa-info-circle me-1"></i> توجيه وإرشاد
                            </span>
                        @endif
                    </td>
                    <td class="fw-bold text-dark">{{ $letter->title }}</td>
                    <td>{{ $letter->created_at->format('Y-m-d') }}</td>
                    <td><small class="text-muted">{{ $letter->sender->name ?? 'مدير المدرسة' }}</small></td>
                    <td class="text-center">
                        <a href="{{ route('manager.letters.print', $letter->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-print me-1"></i> طباعة الشهادة
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-certificate fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                        لا توجد خطابات أو شهادات صادرة لك حالياً.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $letters->links() }}
    </div>
</div>

@endsection

@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #2b5876, #4e4376);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-stamp me-1"></i> المراسلات الرسمية
            </span>
            <h2 class="fw-bold mb-1 text-white">الخطابات وشهادات التقدير الرسمية 📜</h2>
            <p class="mb-0 opacity-75 text-white">
                أرشيف المكاتبات والخطابات الصادرة من مكتب مدير المدرسة للكادر الإداري والتعليمي.
            </p>
        </div>
        <a href="{{ route('manager.letters.create') }}" class="btn btn-warning fw-bold text-dark shadow-sm">
            <i class="fas fa-plus me-1"></i> تحرير خطاب رسمي جديد
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>رقم الصادر / القيد</th>
                    <th>المستلم</th>
                    <th>نوع الخطاب</th>
                    <th>عنوان الخطاب</th>
                    <th>تاريخ الإصدار</th>
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters as $letter)
                <tr>
                    <td>
                        <span class="badge bg-dark-subtle text-dark border font-monospace px-2 py-1">
                            {{ $letter->reference_no }}
                        </span>
                    </td>
                    <td>
                        <strong class="text-dark">{{ $letter->recipient->name ?? 'غير معروف' }}</strong>
                        <small class="text-muted d-block">{{ $letter->recipient->email ?? '' }}</small>
                    </td>
                    <td>
                        @if($letter->type === 'appreciation')
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                <i class="fas fa-award me-1"></i> شكر وتقدير
                            </span>
                        @elseif($letter->type === 'warning')
                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                <i class="fas fa-exclamation-circle me-1"></i> لفت نظر رسمي
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                <i class="fas fa-info-circle me-1"></i> توجيه وإرشاد
                            </span>
                        @endif
                    </td>
                    <td class="fw-bold text-secondary">{{ $letter->title }}</td>
                    <td>{{ $letter->created_at->format('Y-m-d') }}</td>
                    <td class="text-center">
                        <a href="{{ route('manager.letters.print', $letter->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" target="_blank">
                            <i class="fas fa-print me-1"></i> معاينة وطباعة
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                        لم يتم إصدار أي خطابات رسمية بعد. يمكنك إصدار خطاب جديد من الزر أعلاه.
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

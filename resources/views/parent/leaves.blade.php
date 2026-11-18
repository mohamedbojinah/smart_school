@extends('layouts.parent')
@section('title', 'طلبات الاستئذان والغياب')

@section('content')

<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-door-open text-warning me-1"></i> التواصل الإداري المباشر
            </div>
            <h3 class="fw-bold mb-1 text-white">طلبات الاستئذان والغياب الرسمي 📝</h3>
            <p class="mb-0 text-white-50">تقديم إخطارات الغياب المسبق وطلبات الخروج المبكر لمراجعة واعتماد إدارة المدرسة.</p>
        </div>
        <div>
            <button class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                <i class="fas fa-plus"></i> تقديم طلب استئذان جديد
            </button>
        </div>
    </div>
</div>

<div class="card glass-card border-0 overflow-hidden">
    <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history text-primary me-2"></i> أرشيف الطلبات السابقة</h5>
        <span class="badge bg-white border text-secondary px-3 py-2 rounded-pill small">إجمالي الطلبات: {{ $leaves->count() }}</span>
    </div>

    <div class="card-body p-4">
        @if($leaves->count() > 0)
        <div class="table-responsive rounded-4 border overflow-hidden">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light text-secondary small fw-bold">
                    <tr>
                        <th class="py-3 text-start ps-4">اسم الطالب</th>
                        <th>نوع الاستئذان</th>
                        <th>تاريخ الطلب</th>
                        <th class="text-start ps-3">السبب / العذر</th>
                        <th>حالة الاعتماد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                    <tr>
                        <td class="fw-bold text-dark text-start ps-4">
                            <i class="fas fa-user-graduate text-primary me-2"></i> {{ $leave->student->user->name ?? 'غير معروف' }}
                        </td>
                        <td>
                            @if($leave->type == 'absence')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fas fa-calendar-day me-1"></i> غياب يوم كامل
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fas fa-clock me-1"></i> خروج مبكر
                                </span>
                            @endif
                        </td>
                        <td class="text-secondary font-monospace">{{ \Carbon\Carbon::parse($leave->leave_date)->format('Y-m-d') }}</td>
                        <td class="text-start ps-3 text-dark small" style="max-width: 250px;">
                            {{ $leave->reason }}
                        </td>
                        <td>
                            @if($leave->status == 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-hourglass-half me-1"></i> قيد المراجعة
                                </span>
                            @elseif($leave->status == 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-check-circle me-1"></i> تم القبول
                                </span>
                            @elseif($leave->status == 'rejected')
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-times-circle me-1"></i> معتذر / مرفوض
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 bg-white rounded-4 border">
            <i class="fas fa-envelope-open-text fa-3x text-muted opacity-25 mb-3"></i>
            <h6 class="fw-bold text-dark">لا توجد طلبات استئذان سابقة</h6>
            <p class="text-muted small mb-0">يمكنك استخدام الزر أعلاه لإرسال طلب غياب مسبق أو خروج مبكر لأي من أبنائك.</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal Add Leave Modern -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient p-4 text-white" style="background: linear-gradient(135deg, #1e1b4b, #3b82f6);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-envelope-open-text text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold" id="addLeaveModalLabel">تقديم طلب استئذان جديد</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('parent.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">اختر الابن المعني بالطلب:</label>
                        <select name="student_id" class="form-select form-select-lg" required>
                            <option value="">-- اضغط للاختيار --</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}">{{ $child->user->name }} ({{ $child->schoolClass->name ?? 'غير محدد' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">نوع الاستئذان:</label>
                        <select name="type" class="form-select" required>
                            <option value="absence">غياب عن يوم دراسي كامل</option>
                            <option value="early_leave">استئذان خروج مبكر خلال اليوم</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">تاريخ الغياب / الاستئذان:</label>
                        <input type="date" name="leave_date" class="form-control" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">سبب الغياب بالتفصيل:</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="مثال: مراجعة عيادة طبية، ظرف عائلي طارئ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-premium px-4">إرسال الطلب للإدارة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

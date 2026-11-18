@extends('layouts.parent')
@section('title', 'الرسوم المدرسية والدفعات')

@section('content')

<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-file-invoice-dollar text-warning me-1"></i> الشفافية المالية والأقساط
            </div>
            <h3 class="fw-bold mb-1 text-white">كشف الرسوم المدرسية والدفعات 🧾</h3>
            <p class="mb-0 text-white-50">متابعة الأقساط الدراسية، إجمالي المبالغ المسددة، والمبالغ المتبقية مع إيصالات الدفع الرسمية.</p>
        </div>
        <button onclick="window.print()" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
            <i class="fas fa-print text-primary"></i> طباعة الكشف
        </button>
    </div>
</div>

@if($children->count() == 0)
    <div class="glass-card text-center py-5">
        <i class="fas fa-receipt fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="fw-bold text-dark">لا يوجد أبناء مرتبطين بحسابك</h5>
    </div>
@endif

@foreach($children as $child)
<div class="card glass-card mb-4 border-0 overflow-hidden">
    {{-- رأس الطالب --}}
    <div class="p-4 border-bottom bg-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-badge-icon badge-soft-primary mb-0" style="width: 48px; height: 48px;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">{{ $child->user->name ?? 'غير معروف' }}</h5>
                <small class="text-muted"><i class="fas fa-chalkboard me-1"></i> {{ $child->schoolClass->name ?? 'غير محدد' }}</small>
            </div>
        </div>
        @if($child->remaining_fees <= 0)
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">
                <i class="fas fa-check-circle me-1"></i> تم سداد كامل الرسوم
            </span>
        @else
            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold">
                <i class="fas fa-exclamation-circle me-1"></i> يوجد أقساط متبقية
            </span>
        @endif
    </div>

    <div class="card-body p-4">
        {{-- كروت المبالغ المالية --}}
        <div class="row g-3 text-center mb-4">
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 h-100">
                    <small class="text-primary fw-semibold d-block mb-1">إجمالي الرسوم المطلوبة</small>
                    <h3 class="fw-bold text-primary mb-0 font-monospace">{{ number_format($child->total_fees, 2) }} <span class="fs-6">د.ل</span></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 h-100">
                    <small class="text-success fw-semibold d-block mb-1">إجمالي المبلغ المسدد</small>
                    <h3 class="fw-bold text-success mb-0 font-monospace">{{ number_format($child->paid_fees, 2) }} <span class="fs-6">د.ل</span></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 h-100">
                    <small class="text-danger fw-semibold d-block mb-1">المتبقي المطلوب سداده</small>
                    <h3 class="fw-bold text-danger mb-0 font-monospace">{{ number_format($child->remaining_fees, 2) }} <span class="fs-6">د.ل</span></h3>
                </div>
            </div>
        </div>

        {{-- شريط تقدم السداد --}}
        @php
            $payPercent = $child->total_fees > 0 ? min(100, round(($child->paid_fees / $child->total_fees) * 100)) : 100;
        @endphp
        <div class="mb-4">
            <div class="d-flex justify-content-between small text-muted mb-1 fw-semibold">
                <span>نسبة السداد المكتملة</span>
                <span class="text-primary">{{ $payPercent }}%</span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $payPercent }}%" aria-valuenow="{{ $payPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        {{-- جدول الإيصالات --}}
        <h6 class="mb-3 text-dark fw-bold"><i class="fas fa-file-invoice text-primary me-2"></i> سجل الإيصالات والدفعات المسجلة:</h6>
        @if(count($child->feePayments) > 0)
        <div class="table-responsive rounded-4 border overflow-hidden">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light text-secondary small fw-bold">
                    <tr>
                        <th class="py-3">رقم الإيصال</th>
                        <th>المبلغ المسدد</th>
                        <th>تاريخ السداد</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($child->feePayments as $payment)
                    <tr>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace px-3 py-2 fs-6 rounded-pill">#{{ $payment->receipt_number }}</span></td>
                        <td class="text-success fw-bold font-monospace fs-6">{{ number_format($payment->amount, 2) }} د.ل</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                        <td><span class="badge bg-light text-dark border">نقدي / إيداع</span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1"><i class="fas fa-check me-1"></i> معتمد</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center p-4 bg-light rounded-4 border">
            <i class="fas fa-file-invoice text-muted opacity-25 fa-3x mb-2"></i>
            <p class="mb-0 text-muted small">لم يتم تسجيل أي إيصالات سداد لهذا الطالب حتى الآن.</p>
        </div>
        @endif
    </div>
</div>
@endforeach

@endsection

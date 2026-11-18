@extends('layouts.parent')
@section('title', 'المحفظة والمقصف المدرسي')

@section('content')

<div class="page-header-card mb-4 d-print-none">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                <i class="fas fa-wallet text-warning me-1"></i> الدفع الذكي والمقصف المدرسي
            </div>
            <h3 class="fw-bold mb-1 text-white">المحفظة الرقمية ومصروف الأبناء 💳</h3>
            <p class="mb-0 text-white-50">إدارة رصيد الشراء بالمقصف، تعيين الرقم السري الآمن، وضع سقف للمصروف اليومي، وتحويل الرصيد بين الأبناء.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-white px-3 py-2 rounded-pill d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt text-warning"></i> حماية إلكترونية بـ PIN
            </span>
        </div>
    </div>
</div>

@if($children->count() == 0)
    <div class="glass-card text-center py-5">
        <i class="fas fa-wallet fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="fw-bold text-dark">لا يوجد أبناء مرتبطين بحسابك</h5>
    </div>
@endif

<div class="row g-4">
    @foreach($children as $child)
    <div class="col-lg-6">
        <div class="card glass-card h-100 border-0 overflow-hidden">
            
            {{-- رأس بطاقة المحفظة --}}
            <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-badge-icon badge-soft-warning mb-0" style="width: 50px; height: 50px; font-size: 1.3rem;">
                            <i class="fas fa-wallet text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white">{{ $child->user->name ?? 'غير معروف' }}</h5>
                            <small class="text-white-50"><i class="fas fa-store me-1"></i> حساب المقصف الذكي</small>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">بطاقة دفع رقمية</span>
                </div>

                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <small class="text-white-50 d-block">الرصيد المتاح للاستخدام</small>
                        <h2 class="mb-0 fw-bold text-white font-monospace">
                            {{ number_format($child->wallet_balance, 2) }} <span class="fs-6 opacity-75">دينار</span>
                        </h2>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#transferModal-{{ $child->id }}">
                        <i class="fas fa-paper-plane text-primary me-1"></i> تحويل رصيد
                    </button>
                </div>
            </div>

            {{-- إعدادات الأمان وسقف المصروف --}}
            <div class="card-body p-4">
                <div class="row g-3">
                    {{-- 1. الرقم السري PIN --}}
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-key text-primary me-2"></i> الرقم السري للشراء (PIN):</h6>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">4 أرقام</span>
                            </div>
                            <form action="{{ route('parent.wallet.pin') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $child->id }}">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="wallet_pin" class="form-control border-start-0" maxlength="4" placeholder="••••" value="{{ $child->wallet_pin }}" required>
                                </div>
                                <button type="submit" class="btn btn-outline-primary px-3 rounded-3 fw-semibold">تحديث</button>
                            </form>
                            <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                                يتم إدخاله بواسطة الطالب على جهاز المقصف لاعتماد الشراء.
                            </small>
                        </div>
                    </div>

                    {{-- 2. الحد اليومي --}}
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-sliders text-success me-2"></i> سقف المصروف اليومي:</h6>
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                    {{ $child->daily_limit ? number_format($child->daily_limit, 2) . ' د.ل' : 'مفتوح بدون سقف' }}
                                </span>
                            </div>
                            <form action="{{ route('parent.wallet.limit') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $child->id }}">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">د.ل</span>
                                    <input type="number" name="daily_limit" class="form-control border-start-0" step="0.5" min="0" placeholder="0 = غير محدود" value="{{ $child->daily_limit }}">
                                </div>
                                <button type="submit" class="btn btn-outline-success px-3 rounded-3 fw-semibold">حفظ</button>
                            </form>
                            <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                                يمنع الطالب من الشراء بمبلغ يتجاوز هذا الحد في اليوم الواحد.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- نافذة تحويل الرصيد --}}
        <div class="modal fade" id="transferModal-{{ $child->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-exchange-alt text-warning"></i>
                            <h5 class="modal-title fw-bold">تحويل رصيد من ({{ $child->user->name }})</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('parent.wallet.transfer') }}" method="POST">
                        @csrf
                        <input type="hidden" name="from_student_id" value="{{ $child->id }}">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">اختر الابن المستفيد:</label>
                                <select name="to_student_id" class="form-select form-select-lg" required>
                                    <option value="" disabled selected>-- اختر الابن المستلم --</option>
                                    @foreach($children as $sibling)
                                        @if($sibling->id != $child->id)
                                            <option value="{{ $sibling->id }}">
                                                {{ $sibling->user->name }} (رصيده الحالي: {{ number_format($sibling->wallet_balance, 2) }} د.ل)
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">المبلغ المراد تحويله (دينار):</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">د.ل</span>
                                    <input type="number" name="amount" class="form-control" step="0.5" min="1" max="{{ $child->wallet_balance }}" required placeholder="أدخل المبلغ">
                                </div>
                                <small class="text-muted mt-1 d-block">أقصى مبلغ متاح للتحويل: {{ number_format($child->wallet_balance, 2) }} دينار.</small>
                            </div>
                        </div>
                        <div class="modal-footer p-3 bg-light border-0">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-premium px-4">تأكيد التحويل الآن</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

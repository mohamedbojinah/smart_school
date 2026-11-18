@extends('layouts.parent')
@section('title', 'إعدادات الحساب')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <div class="page-header-card mb-4 d-print-none">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold">
                        <i class="fas fa-user-gear text-warning me-1"></i> الأمان والبيانات الشخصية
                    </div>
                    <h3 class="fw-bold mb-1 text-white">إعدادات الحساب الشخصي ⚙️</h3>
                    <p class="mb-0 text-white-50">تحديث بيانات ولي الأمر وتغيير كلمة المرور الخاصة بتسجيل الدخول.</p>
                </div>
                <div class="stat-badge-icon badge-soft-warning mb-0" style="width: 56px; height: 56px; font-size: 1.5rem;">
                    <i class="fas fa-user-lock"></i>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                
                <form action="{{ route('parent.updateProfile') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark"><i class="fas fa-user text-primary me-2"></i> الاسم الكامل لولي الأمر:</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark"><i class="fas fa-envelope text-primary me-2"></i> البريد الإلكتروني المعتمد:</label>
                        <input type="email" class="form-control form-control-lg bg-light rounded-3 font-monospace" value="{{ Auth::user()->email }}" disabled>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> لتغيير البريد الإلكتروني الرسمي، يرجى التواصل مع إدارة النظام.</small>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1"><i class="fas fa-shield-keyhole text-success me-2"></i> تغيير كلمة المرور</h5>
                        <p class="text-muted small">اترك الحقول فارغة أدناه إذا كنت ترغب في الاحتفاظ بكلمة المرور الحالية.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">كلمة المرور الجديدة:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control rounded-start-3" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">تأكيد كلمة المرور الجديدة:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password_confirmation" class="form-control rounded-start-3" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-premium px-5 py-2">
                            <i class="fas fa-floppy-disk me-2"></i> حفظ التغييرات
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection

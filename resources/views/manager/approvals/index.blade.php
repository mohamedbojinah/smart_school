@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-success px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-check-double me-1"></i> السلطة التنفيذية
            </span>
            <h2 class="fw-bold mb-1 text-white">مركز الاعتمادات والقرارات الإدارية ✍️</h2>
            <p class="mb-0 opacity-75 text-white">
                إصدار ومصادقة القرارات الحساسة: إعلان النتائج والشهادات، ترحيل الطلاب، واعتماد الموازنات.
            </p>
        </div>
        <button type="button" class="btn btn-warning fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#newApprovalModal">
            <i class="fas fa-plus me-1"></i> تسجيل قرار جديد للمصادقة
        </button>
    </div>
</div>

<div class="row g-4">
    {{-- بطاقات القرارات الرئيسية السريعة --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white text-center h-100">
            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-file-signature fs-3"></i>
            </div>
            <h5 class="fw-bold text-dark">اعتماد إعلان النتائج</h5>
            <p class="text-muted small">المصادقة الرسمية على نشر كشوف العلامات والشهادات لأولياء الأمور والطلاب.</p>
            <form action="{{ route('manager.approvals.store') }}" method="POST" class="mt-auto">
                @csrf
                <input type="hidden" name="type" value="publish_grades">
                <input type="hidden" name="title" value="اعتماد إعلان نتائج الفصل الدراسي والشهادات النهائية">
                <input type="hidden" name="description" value="المصادقة على دقة الرصد ونشر الشهادات في حسابات الطلاب وأولياء الأمور.">
                <button type="submit" class="btn btn-outline-primary w-100 fw-bold">
                    <i class="fas fa-stamp me-1"></i> تدشين طلب اعتماد النتائج
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white text-center h-100">
            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-user-graduate fs-3"></i>
            </div>
            <h5 class="fw-bold text-dark">اعتماد ترحيل الطلاب</h5>
            <p class="text-muted small">الموافقة على ترحيل الطلاب الناجحين للعام الدراسي القادم وإبقاء الراسبين.</p>
            <form action="{{ route('manager.approvals.store') }}" method="POST" class="mt-auto">
                @csrf
                <input type="hidden" name="type" value="promote_students">
                <input type="hidden" name="title" value="اعتماد خطة الترحيل الجماعي السنوي للطلاب الناجحين">
                <input type="hidden" name="description" value="المصادقة على نتائج نهاية العام والترحيل الآلي للفصول اللاحقة.">
                <button type="submit" class="btn btn-outline-success w-100 fw-bold">
                    <i class="fas fa-stamp me-1"></i> تدشين طلب اعتماد الترحيل
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white text-center h-100">
            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-coins fs-3"></i>
            </div>
            <h5 class="fw-bold text-dark">اعتماد موازنة أو مصروف خاص</h5>
            <p class="text-muted small">الموافقة التنفيذية على العمليات المالية الاستثنائية وتحديثات الميزانية.</p>
            <button type="button" class="btn btn-outline-warning text-dark w-100 fw-bold mt-auto" data-bs-toggle="modal" data-bs-target="#newApprovalModal">
                <i class="fas fa-edit me-1"></i> إدخال قرار مالي مخصص
            </button>
        </div>
    </div>
</div>

{{-- جدول القرارات والاعتمادات --}}
<div class="card border-0 shadow-sm p-4 bg-white mt-4">
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-list-check text-primary me-2"></i> سجل القرارات الإدارية والاعتمادات</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>نوع القرار</th>
                    <th>موضوع القرار</th>
                    <th>التفاصيل والمبررات</th>
                    <th>الحالة</th>
                    <th>تاريخ الاعتماد</th>
                    <th class="text-center">إجراء المدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvals as $app)
                <tr>
                    <td>
                        <span class="badge bg-secondary-subtle text-dark border">
                            {{ $app->type }}
                        </span>
                    </td>
                    <td class="fw-bold text-dark">{{ $app->title }}</td>
                    <td class="small text-muted" style="max-width: 300px;">{{ $app->description ?? '-' }}</td>
                    <td>
                        @if($app->status === 'approved')
                            <span class="badge bg-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i> معتمد رسمي
                            </span>
                        @elseif($app->status === 'rejected')
                            <span class="badge bg-danger px-3 py-2">
                                <i class="fas fa-times-circle me-1"></i> مرفوض
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-2">
                                <i class="fas fa-clock me-1"></i> قيد المراجعة
                            </span>
                        @endif
                    </td>
                    <td class="small text-muted">
                        {{ $app->action_date ? $app->action_date->format('Y-m-d H:i') : 'في الانتظار' }}
                    </td>
                    <td class="text-center">
                        @if($app->status === 'pending')
                            <div class="d-inline-flex gap-2">
                                <form action="{{ route('manager.approvals.update', $app->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <input type="hidden" name="notes" value="تم الاعتماد والمصادقة من قبل مدير المدرسة.">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm">
                                        <i class="fas fa-check me-1"></i> اعتماد
                                    </button>
                                </form>
                                <form action="{{ route('manager.approvals.update', $app->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="hidden" name="notes" value="تم الرفض للمراجعة وإعادة التنسيق.">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times me-1"></i> رفض
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted small">مكتمل بالقرار: {{ $app->notes }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        لا توجد طلبات اعتماد مسجلة حالياً. استخدم الأزرار أعلاه لإنشاء قرار إداري جديد.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $approvals->links() }}
    </div>
</div>

{{-- مودال إضافة قرار جديد مخصص --}}
<div class="modal fade" id="newApprovalModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('manager.approvals.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">تسجيل قرار إداري أو مالي للاعتماد</h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-end p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">تصنيف القرار:</label>
                        <select name="type" class="form-select text-end" required>
                            <option value="publish_grades">اعتماد إعلان نتائج وشهادات</option>
                            <option value="promote_students">اعتماد ترحيل طلاب سنوي</option>
                            <option value="major_expense">اعتماد ميزانية / مصروفات خاصة</option>
                            <option value="policy_change">اعتماد لائحة تنظيمية داخلية</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان وموضوع القرار:</label>
                        <input type="text" name="title" class="form-control text-end" placeholder="مثال: اعتماد شراء أجهزة حاسوب للمختبر العلمي" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الشرح والمبررات:</label>
                        <textarea name="description" rows="4" class="form-control text-end" placeholder="اكتب تفاصيل القرار ومبرراته ليتم توثيقها في الأرشيف الرسمي..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fas fa-save me-1"></i> حفظ وإدراج القرار
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

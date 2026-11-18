@extends(auth()->user()->role === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #11998e, #38ef7d); color: #fff;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-dark text-white px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-shield-alt me-1"></i> وحدة الإرشاد الطلابي والرفاهية
            </span>
            <h2 class="fw-bold mb-1 text-white">صندوق الأمان المدرسي ومكافحة التنمر 🛡️</h2>
            <p class="mb-0 text-white-50">
                متابعة بلاغات الطلاب السرية، التصدي الفوري للتنمر، وتحليل نبض الرفاهية النفسية لطلاب المدرسة.
            </p>
        </div>
        <div class="d-none d-md-block text-white opacity-25">
            <i class="fas fa-heart-pulse fa-4x"></i>
        </div>
    </div>
</div>

{{-- بطاقات الإحصاءات السريعة --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-top border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small fw-bold">إجمالي البلاغات</span>
                <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="fas fa-inbox"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark mt-2">{{ $stats['total'] }}</h3>
            <span class="text-muted small">كل الحالات المسجلة</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-top border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small fw-bold">تنتظر مراجعة المرشد</span>
                <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="fas fa-clock"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-warning mt-2">{{ $stats['pending'] }}</h3>
            <span class="text-muted small">تتطلب رداً أو متابعة</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-top border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small fw-bold">حالات حرجة وطارئة</span>
                <span class="badge bg-danger-subtle text-danger p-2 rounded-circle"><i class="fas fa-triangle-exclamation"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-danger mt-2">{{ $stats['critical'] }}</h3>
            <span class="text-danger small">تستدعي تدخلاً عاجلاً</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-top border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small fw-bold">حالات تنمر مرصودة</span>
                <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="fas fa-hand-holding-heart"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-info mt-2">{{ $stats['bullying'] }}</h3>
            <span class="text-muted small">رصد مكافحة التنمر</span>
        </div>
    </div>
</div>

{{-- تحليل نبض المشاعر والرفاهية الأسبوعي مع جدول البلاغات --}}
<div class="row g-4 mb-4">
    {{-- رسم بياني: نبض المشاعر --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-smile-beam text-warning me-2"></i> نبض المشاعر هذا الأسبوع</h5>
            <small class="text-muted d-block mb-3">توزيع استبيان رفاهية الطلاب النفسية الأسبوعي</small>
            
            <div style="position: relative; height: 240px;" class="d-flex align-items-center justify-content-center">
                <canvas id="moodPulseChart"></canvas>
            </div>
        </div>
    </div>

    {{-- جدول البلاغات الواردة --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-shield-alt text-primary me-2"></i> سجل البلاغات والاستشارات</h5>
                <div class="d-flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['urgency' => 'critical']) }}" class="btn btn-sm btn-outline-danger">الحرجة فقط</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning">المعلقة فقط</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الرمز السري</th>
                            <th>الطالب</th>
                            <th>التصنيف</th>
                            <th>الأهمية</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th class="text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                        <tr class="{{ $rep->urgency === 'critical' ? 'table-danger' : '' }}">
                            <td><span class="badge bg-dark-subtle text-dark font-monospace">{{ $rep->token }}</span></td>
                            <td>
                                @if($rep->student)
                                    <strong class="text-dark">{{ $rep->student->name }}</strong>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-user-secret me-1"></i> مجهول الهوية 🔒</span>
                                @endif
                            </td>
                            <td>
                                @if($rep->category === 'bullying')
                                    <span class="badge bg-danger-subtle text-danger border border-danger">تنمر</span>
                                @elseif($rep->category === 'mental_stress')
                                    <span class="badge bg-warning-subtle text-warning border border-warning">ضغط نفسي</span>
                                @elseif($rep->category === 'academic_pressure')
                                    <span class="badge bg-info-subtle text-info border border-info">تعثر دراسي</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $rep->category }}</span>
                                @endif
                            </td>
                            <td>
                                @if($rep->urgency === 'critical')
                                    <span class="badge bg-danger">حرج وطارئ</span>
                                @elseif($rep->urgency === 'high')
                                    <span class="badge bg-warning text-dark">هام</span>
                                @else
                                    <span class="badge bg-light text-dark border">عادي</span>
                                @endif
                            </td>
                            <td>
                                @if($rep->status === 'resolved')
                                    <span class="badge bg-success">تم المعالجة</span>
                                @elseif($rep->status === 'in_review')
                                    <span class="badge bg-warning text-dark">قيد الإجراء</span>
                                @else
                                    <span class="badge bg-secondary">جديد معلق</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $rep->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#respondModal{{ $rep->id }}">
                                    <i class="fas fa-reply me-1"></i> مراجعة ورد
                                </button>

                                {{-- مودال مراجعة البلاغ والرد عليه --}}
                                <div class="modal fade" id="respondModal{{ $rep->id }}" tabindex="-1" aria-hidden="true" dir="rtl">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content text-start">
                                            @php
                                                $actionRoute = auth()->user()->role === 'manager' 
                                                    ? route('manager.safebox.respond', $rep->id) 
                                                    : route('admin.safebox.respond', $rep->id);
                                            @endphp
                                            <form action="{{ $actionRoute }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="fas fa-shield-alt text-primary me-1"></i> مراجعة البلاغ: {{ $rep->token }}
                                                    </h5>
                                                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-end p-4">
                                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <strong class="text-primary">{{ $rep->title }}</strong>
                                                            <span class="badge bg-secondary">{{ $rep->category }}</span>
                                                        </div>
                                                        <p class="text-dark small mb-0" style="white-space: pre-wrap;">{{ $rep->description }}</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">حالة البلاغ بعد المراجعة:</label>
                                                        <select name="status" class="form-select text-end" required>
                                                            <option value="in_review" {{ $rep->status === 'in_review' ? 'selected' : '' }}>قيد المتابعة والتوجيه (In Review)</option>
                                                            <option value="resolved" {{ $rep->status === 'resolved' ? 'selected' : '' }}>تم الحل والمعالجة بنجاح (Resolved)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">رد وتوجيه المرشد الطلابي / الإدارة (يظهر للطالب برمز التتبع):</label>
                                                        <textarea name="counselor_response" rows="4" class="form-control text-end" placeholder="اكتب رداً مشجعاً ومطمئناً للطالب وخطوات الإجراء..." required>{{ $rep->counselor_response }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light d-flex justify-content-between">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    <button type="submit" class="btn btn-success fw-bold">
                                                        <i class="fas fa-paper-plane me-1"></i> حفظ وتحديث الرد السري
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-shield-heart fa-3x mb-2 text-success opacity-50 d-block"></i>
                                لا توجد بلاغات مسجلة، البيئة المدرسية هادئة وآمنة! 🌟
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('moodPulseChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($moodLabels) !!},
                datasets: [{
                    data: {!! json_encode($moodData) !!},
                    backgroundColor: ['#20c997', '#0dcaf0', '#ffc107', '#6c757d', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { family: 'Tahoma' } }
                    }
                }
            }
        });
    }
});
</script>

@endsection

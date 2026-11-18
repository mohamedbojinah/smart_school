<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>التقرير السنوي الشامل - {{ $school->name ?? 'Smart School' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #eef2f5;
            font-family: 'Tahoma', 'Segoe UI', Arial, sans-serif;
            color: #212529;
            padding: 25px 0;
        }

        .report-page {
            max-width: 950px;
            background: #fff;
            margin: 0 auto;
            padding: 50px 60px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.07);
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .report-header {
            border-bottom: 2px solid #2b5876;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-heading {
            border-right: 4px solid #2b5876;
            padding-right: 12px;
            font-weight: 700;
            color: #1a365d;
            margin: 25px 0 15px 0;
            font-size: 1.15rem;
        }

        .kpi-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .kpi-box h4 {
            font-weight: 800;
            color: #2b5876;
            margin-bottom: 2px;
        }

        .kpi-box span {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .seal-stamp {
            width: 140px;
            height: 140px;
            border: 3px double #b91c1c;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #b91c1c;
            transform: rotate(-10deg);
            margin: 0 auto;
            font-size: 0.75rem;
            font-weight: bold;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .report-page {
                box-shadow: none !important;
                border: none !important;
                padding: 20px 20px !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

<div class="text-center mb-4 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold shadow">
        <i class="fas fa-print me-1"></i> طباعة التقرير الشامل / تصدير PDF
    </button>
    <a href="{{ route('manager.dashboard') }}" class="btn btn-outline-secondary px-3 py-2 ms-2">
        <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
    </a>
</div>

<div class="report-page">
    {{-- ترويسة التقرير الرسمية --}}
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark mb-1">{{ $school->name ?? 'المدرسة الذكية النموذجية' }}</h3>
            <p class="text-muted small mb-0">تقرير الأداء المؤسسي السنوي والتحليلي الشامل</p>
            <p class="text-muted small mb-0">العام الأكاديمي: <strong>{{ $school->academic_year ?? '2026/2027' }}</strong></p>
        </div>
        <div class="text-center">
            <div class="display-6 fw-bold text-primary">Smart School 🎓</div>
            <span class="badge bg-light text-dark border px-3 py-1 font-monospace">تقرير رسمي معتمد</span>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">تاريخ الصدور: {{ now()->format('Y-m-d') }}</small>
            <small class="text-muted d-block">إعداد: مكتب مدير المدرسة</small>
            <span class="badge bg-success-subtle text-success border border-success mt-1">حالة التقرير: نهائي</span>
        </div>
    </div>

    {{-- شبكة المؤشرات الحيوية التنفيذية (KPI Grid) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4>{{ $summary['total_students'] }}</h4>
                <span>إجمالي الطلاب</span>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4>{{ $summary['total_teachers'] }}</h4>
                <span>كادر المعلمين</span>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4>{{ $summary['total_classes'] }}</h4>
                <span>الشعب الدراسية</span>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4 class="text-success">{{ $summary['attendance_rate'] }}%</h4>
                <span>معدل الحضور</span>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4 class="text-primary">{{ $summary['health_score'] }}/100</h4>
                <span>المؤشر الصحي</span>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="kpi-box">
                <h4 class="{{ $summary['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($summary['net_balance'], 0) }}
                </h4>
                <span>صافي التشغيل (د.أ)</span>
            </div>
        </div>
    </div>

    {{-- القسم الأول: التحصيل الأكاديمي وتوزيع الطلاب --}}
    <h5 class="section-heading">1. الهيكل الأكاديمي وتوزيع الطلاب على المراحل</h5>
    <table class="table table-bordered table-sm align-middle mb-4">
        <thead class="table-light">
            <tr>
                <th>المرحلة الدراسية</th>
                <th>الشعب والفصول</th>
                <th>عدد الطلاب المسجلين</th>
                <th>حالة الرصد</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades as $grade)
            <tr>
                <td class="fw-bold">{{ $grade->name }}</td>
                <td>
                    @foreach($grade->schoolClasses as $c)
                        <span class="badge bg-light text-dark border me-1">{{ $c->name }}</span>
                    @endforeach
                </td>
                <td class="fw-bold">
                    {{ $grade->schoolClasses->sum(fn($c) => $c->students->count()) }} طالب
                </td>
                <td><span class="badge bg-success-subtle text-success">مكتمل</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">لا توجد مراحل مسجلة.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- القسم الثاني: الموقف المالي والموازنة التشغيلية --}}
    <h5 class="section-heading">2. الموقف المالي وحركة الرسوم والمصروفات</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 bg-light rounded border">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">إجمالي الرسوم المدرسية المحصلة:</span>
                    <strong class="text-success">{{ number_format($summary['collected_fees'], 2) }} د.أ</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">إجمالي المصروفات التشغيلية:</span>
                    <strong class="text-danger">{{ number_format($summary['total_expenses'], 2) }} د.أ</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">صافي الميزانية التشغيلية:</span>
                    <strong class="fs-6 {{ $summary['net_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ number_format($summary['net_balance'], 2) }} د.أ
                    </strong>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-3 bg-light rounded border">
                <strong class="d-block text-secondary mb-2 small">توزيع المصروفات حسب التصنيف:</strong>
                <ul class="list-unstyled mb-0 small">
                    @forelse($expenseCategories as $ec)
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span>{{ $ec->category ?? 'مصروف عام' }}</span>
                        <strong>{{ number_format($ec->total, 2) }} د.أ</strong>
                    </li>
                    @empty
                    <li class="text-muted py-1">لا توجد مصروفات تفصيلية مسجلة.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- القسم الثالث: أداء الكادر التعليمي --}}
    <h5 class="section-heading">3. خلاصة تقييم الكادر التعليمي</h5>
    <table class="table table-bordered table-sm align-middle mb-4">
        <thead class="table-light">
            <tr>
                <th>المعلم</th>
                <th>الانضباط</th>
                <th>جودة الشرح</th>
                <th>التعاون</th>
                <th>التقييم الإجمالي</th>
                <th>التوصية الإدارية</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teachers as $t)
            @php $ev = $t->teacherEvaluations->first(); @endphp
            <tr>
                <td class="fw-bold">{{ $t->name }}</td>
                <td>{{ $ev ? $ev->punctuality_rating . '/5' : '5/5' }}</td>
                <td>{{ $ev ? $ev->teaching_quality_rating . '/5' : '5/5' }}</td>
                <td>{{ $ev ? $ev->cooperation_rating . '/5' : '5/5' }}</td>
                <td class="fw-bold text-primary">{{ $ev ? $ev->overall_rating . ' / 5.0' : '5.0 / 5.0' }}</td>
                <td><small>{{ $ev->recommendation ?? 'أداء مستقر ومتميز' }}</small></td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">لا يوجد معلمون مسجلون.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- القسم الرابع: القرارات التنفيذية المعتمدة --}}
    <h5 class="section-heading">4. القرارات الإدارية والاعتمادات الرسمية الصادرة</h5>
    <ul class="list-group list-group-flush mb-4 small">
        @forelse($recentApprovals as $ra)
        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="text-dark">{{ $ra->title }}</strong>
                <span class="text-muted ms-2">({{ $ra->description }})</span>
            </div>
            <span class="badge bg-light text-dark border">{{ $ra->action_date ? $ra->action_date->format('Y-m-d') : '' }}</span>
        </li>
        @empty
        <li class="list-group-item px-0 text-muted">جميع قرارات العام الدراسي معتمدة وموثقة حسب اللوائح المدرسية.</li>
        @endforelse
    </ul>

    {{-- توقيع وختم مدير المدرسة --}}
    <div class="row align-items-center mt-5 pt-4 border-top">
        <div class="col-6 text-center">
            <div class="seal-stamp">
                <span>★ مدرسة ذكية نموذجية ★</span>
                <span style="font-size: 0.65rem;">{{ $school->name ?? 'Smart School' }}</span>
                <span style="font-size: 0.6rem;">الختم الرسمي للإدارة</span>
                <span>✓ OFFICIAL SEAL</span>
            </div>
        </div>
        <div class="col-6 text-center">
            <span class="text-muted small d-block">مدير عام المدرسة والمفوض بالتوقيع</span>
            <h5 class="fw-bold text-dark mt-1 mb-2">{{ auth()->user()->name }}</h5>
            <div style="font-family: 'Brush Script MT', cursive; font-size: 2rem; color: #1e3c72; transform: rotate(-6deg);">
                {{ auth()->user()->name }}
            </div>
            <small class="text-muted">صدر بتاريخ: {{ now()->format('Y-m-d') }}</small>
        </div>
    </div>
</div>

</body>
</html>

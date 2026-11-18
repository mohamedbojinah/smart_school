<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سجل إنجاز الطالب: {{ $student->name }} - {{ $school->name ?? 'Smart School' }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Tahoma', 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
            padding: 30px 0;
        }

        .showcase-container {
            max-width: 960px;
            margin: 0 auto;
        }

        .hero-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 1.5rem;
            color: #fff;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.2);
        }

        .hero-pattern {
            position: absolute;
            top: -20px;
            left: -20px;
            font-size: 15rem;
            color: rgba(255, 255, 255, 0.04);
            pointer-events: none;
            user-select: none;
        }

        .avatar-box {
            width: 110px;
            height: 110px;
            background: #fff;
            color: #1e3c72;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border: 4px solid rgba(255,255,255,0.4);
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(32, 201, 151, 0.2);
            color: #20c997;
            border: 1px solid #20c997;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .highlight-stat {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            text-align: center;
            transition: 0.3s;
        }
        .highlight-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .project-card {
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: 0.2s;
        }
        .project-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.08);
        }

        .skill-pill {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 4px;
        }

        .official-seal {
            width: 130px;
            height: 130px;
            border: 3px double #b91c1c;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #b91c1c;
            transform: rotate(-10deg);
            margin: 0 auto;
            font-size: 0.72rem;
            font-weight: bold;
        }

        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .showcase-container { max-width: 100% !important; }
            .hero-card { box-shadow: none !important; border: 1px solid #2b5876; }
        }
    </style>
</head>
<body>

<div class="showcase-container px-3">

    {{-- أزرار المشاركة والطباعة العلوية --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-home me-1"></i> بوابة المدرسة
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow">
                <i class="fas fa-print me-1"></i> طباعة السجل الرسمي (A4 CV)
            </button>
        </div>
    </div>

    {{-- بطاقة الترويسة الفاخرة للطالب --}}
    <div class="hero-card mb-4">
        <i class="fas fa-graduation-cap hero-pattern"></i>
        <div class="row align-items-center position-relative">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-4 mb-3">
                    <div class="avatar-box">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="verified-badge mb-2">
                            <i class="fas fa-certificate"></i> سجل إنجاز رقمي معتمد رسمياً
                        </div>
                        <h2 class="fw-bold mb-1">{{ $student->name }}</h2>
                        <p class="text-white-50 mb-0">
                            {{ $school->name ?? 'المدرسة الذكية النموذجية' }} | 
                            {{ $student->studentProfile && $student->studentProfile->schoolClass ? $student->studentProfile->schoolClass->name : 'طالب مدرسي' }}
                        </p>
                    </div>
                </div>
                <h5 class="fw-bold text-warning mb-2">{{ $portfolio->headline }}</h5>
                <p class="text-white-50 small mb-0" style="line-height: 1.8;">{{ $portfolio->about_me }}</p>
            </div>

            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-25 d-inline-block text-center text-lg-start">
                    <small class="text-white-50 d-block">رمز التحقق المعتمد</small>
                    <strong class="font-monospace fs-6 text-white">{{ $portfolio->verification_seal_number }}</strong>
                    <hr class="my-2 border-white opacity-25">
                    <small class="text-white-50 d-block">العام الأكاديمي</small>
                    <strong class="text-warning">{{ $school->academic_year ?? '2026/2027' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- إحصائيات الإنجاز وساعات التطوع --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="highlight-stat h-100">
                <i class="fas fa-hand-holding-heart fa-2x text-success mb-2"></i>
                <h3 class="fw-bold text-dark mb-0">{{ $portfolio->volunteer_hours }}</h3>
                <span class="text-muted small">ساعات الخدمة المجتمعية والتطوع</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="highlight-stat h-100">
                <i class="fas fa-laptop-code fa-2x text-primary mb-2"></i>
                <h3 class="fw-bold text-dark mb-0">{{ $portfolio->items->where('category', 'project')->count() }}</h3>
                <span class="text-muted small">مشاريع وابتكارات منجزة</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="highlight-stat h-100">
                <i class="fas fa-award fa-2x text-warning mb-2"></i>
                <h3 class="fw-bold text-dark mb-0">{{ $portfolio->items->whereIn('category', ['certificate', 'competition'])->count() }}</h3>
                <span class="text-muted small">شهادات وتكريمات مسجلة</span>
            </div>
        </div>
    </div>

    {{-- المهارات والقدرات الشخصية --}}
    @if(!empty($portfolio->skills_array))
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-brain text-primary me-2"></i> المهارات والكفاءات الشخصية</h5>
        <div>
            @foreach($portfolio->skills_array as $skill)
                <span class="skill-pill"><i class="fas fa-check text-primary me-1"></i> {{ $skill }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- معرض المشاريع والإنجازات الموثقة --}}
    <div class="card border-0 shadow-sm p-4 bg-white rounded-4 mb-4">
        <h5 class="fw-bold text-dark mb-4"><i class="fas fa-folder-open text-warning me-2"></i> سجل المشاريع والشهادات المعتمدة</h5>

        @forelse($portfolio->items as $item)
        <div class="project-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    @if($item->category === 'project')
                        <span class="badge bg-primary px-3 py-1"><i class="fas fa-laptop-code me-1"></i> مشروع علمي</span>
                    @elseif($item->category === 'certificate')
                        <span class="badge bg-success px-3 py-1"><i class="fas fa-award me-1"></i> شهادة تميز</span>
                    @elseif($item->category === 'competition')
                        <span class="badge bg-warning text-dark px-3 py-1"><i class="fas fa-medal me-1"></i> أولمبياد ومنافسات</span>
                    @elseif($item->category === 'research')
                        <span class="badge bg-info text-dark px-3 py-1"><i class="fas fa-microscope me-1"></i> بحث ومقال</span>
                    @else
                        <span class="badge bg-secondary px-3 py-1"><i class="fas fa-palette me-1"></i> موهبة ونشاط</span>
                    @endif
                    <span class="badge bg-light text-dark border ms-2">{{ $item->year }}</span>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-2">{{ $item->title }}</h5>
            <p class="text-secondary small mb-3" style="line-height: 1.8;">{{ $item->description }}</p>

            @if($item->project_url)
                <a href="{{ $item->project_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill no-print">
                    <i class="fas fa-external-link-alt me-1"></i> زيارة نموذج المشروع
                </a>
            @endif
        </div>
        @empty
        <div class="text-center py-4 text-muted">
            <p class="mb-0">سجل الإنجازات قيد التحديث من قبل الطالب.</p>
        </div>
        @endforelse
    </div>

    {{-- ختم المصادقة المدرسية الرسمي --}}
    <div class="row align-items-center mt-5 pt-4 border-top">
        <div class="col-6 text-center">
            <div class="official-seal">
                <span>★ مصادقة واعتماد ★</span>
                <span style="font-size: 0.65rem;">{{ $school->name ?? 'Smart School' }}</span>
                <span style="font-size: 0.6rem;">وثيقة إنجاز رسمية</span>
                <span>✓ VERIFIED PORTFOLIO</span>
            </div>
        </div>
        <div class="col-6 text-center">
            <span class="text-muted small d-block">إدارة مدرسة</span>
            <h5 class="fw-bold text-dark mt-1 mb-1">{{ $school->name ?? 'المدرسة الذكية النموذجية' }}</h5>
            <small class="text-muted d-block">رقم القيد المركزي: {{ $portfolio->verification_seal_number }}</small>
            <small class="text-muted">صدر بتاريخ: {{ now()->format('Y-m-d') }}</small>
        </div>
    </div>

</div>

</body>
</html>

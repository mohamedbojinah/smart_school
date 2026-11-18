<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>خطاب رسمي - {{ $letter->reference_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #eef2f5;
            font-family: 'Tahoma', 'Segoe UI', Arial, sans-serif;
            color: #212529;
            padding: 30px 0;
        }

        .letter-container {
            max-width: 800px;
            background: #fff;
            margin: 0 auto;
            padding: 60px 70px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 8px;
            border: 1px solid #dee2e6;
            position: relative;
        }

        .header-logo {
            font-size: 2.2rem;
            color: #1a365d;
            font-weight: 800;
        }

        .letter-frame {
            border: 2px solid #2b5876;
            padding: 40px;
            border-radius: 6px;
            position: relative;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5rem;
            color: rgba(43, 88, 118, 0.04);
            font-weight: bold;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
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
            transform: rotate(-12deg);
            margin: 0 auto;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 0 10px rgba(185, 28, 28, 0.1);
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .letter-container {
                box-shadow: none !important;
                border: none !important;
                padding: 30px 40px !important;
            }
        }
    </style>
</head>
<body>

<div class="text-center mb-4 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold shadow">
        <i class="fas fa-print me-1"></i> طباعة الخطاب الرسمي
    </button>
    <a href="{{ route('manager.letters.index') }}" class="btn btn-outline-secondary px-3 py-2 ms-2">
        <i class="fas fa-arrow-right me-1"></i> رجوع
    </a>
</div>

<div class="letter-container">
    <div class="letter-frame">
        <div class="watermark">{{ $school->name ?? 'SMART SCHOOL' }}</div>

        {{-- ترويسة الخطاب الرسمية --}}
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $school->name ?? 'المدرسة الذكية النموذجية' }}</h5>
                <small class="text-muted d-block">مكتب الإدارة العامة ومدير المدرسة</small>
                <small class="text-muted d-block">العام الدراسي: {{ $school->academic_year ?? '2026/2027' }}</small>
            </div>
            <div class="text-center">
                <div class="header-logo">Smart School 🎓</div>
                <span class="badge bg-dark-subtle text-dark border px-2 py-1 font-monospace">
                    رقم القيد: {{ $letter->reference_no }}
                </span>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">التاريخ: {{ $letter->created_at->format('Y-m-d') }}</small>
                <small class="text-muted d-block">الموافق: {{ $letter->created_at->locale('ar')->isoFormat('dddd') }}</small>
                <small class="text-muted d-block">الحالة: صادرة ومعتمدة</small>
            </div>
        </div>

        {{-- شريط تصنيف الخطاب --}}
        <div class="text-center my-4">
            @if($letter->type === 'appreciation')
                <h3 class="fw-bold text-success border-bottom border-success d-inline-block pb-2 px-4">
                    ✨ شــهـادة شـكـر وتـقـديـر ✨
                </h3>
            @elseif($letter->type === 'warning')
                <h3 class="fw-bold text-danger border-bottom border-danger d-inline-block pb-2 px-4">
                    ⚠️ خـطـاب لـفـت نـظـر رسـمـي ⚠️
                </h3>
            @else
                <h3 class="fw-bold text-primary border-bottom border-primary d-inline-block pb-2 px-4">
                    ℹ️ خـطـاب تـوجـيـه وإرشــاد ℹ️
                </h3>
            @endif
        </div>

        {{-- نص الخطاب والمخاطبة --}}
        <div class="my-4">
            <h5 class="fw-bold mb-3 text-dark">
                إلى المكرم / الأستاذ(ة): <span class="text-decoration-underline text-primary">{{ $letter->recipient->name ?? 'عضو الكادر' }}</span> المحترم،
            </h5>
            <p class="text-muted mb-4">السلام عليكم ورحمة الله وبركاته، وبعد ...</p>
            
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-dark mb-2">الموضوع: {{ $letter->title }}</h6>
                <div class="fs-6 text-dark" style="line-height: 2.1; text-align: justify;">
                    {{ $letter->content }}
                </div>
            </div>

            <p class="text-center text-muted mt-4">شاكرين ومقدرين حسن تعاونكم وحرصكم الدائم على الارتقاء بالمسيرة التعليمية.</p>
        </div>

        {{-- التوقيع والختم المعتمد --}}
        <div class="row align-items-center mt-5 pt-4 border-top">
            <div class="col-6 text-center">
                <div class="official-seal">
                    <span>★ ختم الإدارة ★</span>
                    <span style="font-size: 0.65rem;">{{ $school->name ?? 'Smart School' }}</span>
                    <span style="font-size: 0.6rem;">معتمد إلكترونياً</span>
                    <span>✓ APPROVED</span>
                </div>
            </div>
            <div class="col-6 text-center">
                <span class="text-muted small d-block">مدير المدرسة</span>
                <h5 class="fw-bold text-dark mt-1 mb-3">{{ $letter->sender->name ?? 'مدير المدرسة' }}</h5>
                <div style="font-family: 'Brush Script MT', cursive; font-size: 1.8rem; color: #1e3c72; transform: rotate(-5deg);">
                    {{ $letter->sender->name ?? 'Principal' }}
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>

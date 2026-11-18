<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | منظومة التحقق الرقمي</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap RTL & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #1e293b;
            padding: 30px 15px;
        }
        .cert-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        .cert-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: white;
            padding: 35px 25px;
            position: relative;
        }
        .security-badge {
            background: rgba(16, 185, 129, 0.15);
            border: 2px solid #10b981;
            color: #065f46;
            padding: 12px 24px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 7rem;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.03);
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        .detail-row {
            padding: 14px 18px;
            border-bottom: 1px dashed #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .detail-val {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
        }
        .official-seal {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px dashed #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #0284c7;
            font-size: 0.75rem;
            font-weight: 800;
            margin: 0 auto;
            transform: rotate(-10deg);
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .cert-card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 680px;">
    <div class="text-center mb-4 no-print">
        <a href="/" class="text-white-50 text-decoration-none fw-bold small">
            <i class="fas fa-arrow-right me-1"></i> العودة للمنصة الرئيسية
        </a>
    </div>

    @if($document)
        <div class="cert-card">
            <!-- Watermark -->
            <div class="watermark">SMART SCHOOL</div>

            <!-- Header -->
            <div class="cert-header text-center">
                <div class="mb-2">
                    <i class="fas fa-award fa-3x text-warning"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ $document['school_name'] }}</h3>
                <p class="mb-0 text-white-50 small">بوابة التحقق الرقمي المعتمدة | Electronic Document Verification</p>
            </div>

            <!-- Body -->
            <div class="p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="security-badge mb-2">
                        <i class="fas fa-shield-check fa-lg text-success me-2"></i>
                        وثيقة رسمية صحيحة ومعتمدة 100%
                    </div>
                    <p class="text-muted small mb-0">تم التحقق من تطابق الرقم التسلسلي والتوقيع الرقمي مع قاعدة بيانات المدرسة الرسمية.</p>
                </div>

                <div class="card bg-light border-0 rounded-4 mb-4">
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-file-signature text-primary me-2"></i> نوع الوثيقة</span>
                        <span class="detail-val text-primary">{{ $document['type'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-hashtag text-secondary me-2"></i> الرقم المرجعي</span>
                        <span class="detail-val font-monospace">{{ $document['number'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-user-graduate text-info me-2"></i> صاحب الوثيقة</span>
                        <span class="detail-val">{{ $document['student_name'] }}</span>
                    </div>
                    @if(isset($document['class_name']))
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-chalkboard-teacher text-dark me-2"></i> الصف / الشعبة</span>
                        <span class="detail-val">{{ $document['class_name'] }}</span>
                    </div>
                    @endif
                    @if(isset($document['amount']))
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-money-bill-wave text-success me-2"></i> المبلغ المعتمد</span>
                        <span class="detail-val text-success fw-bold">{{ $document['amount'] }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-calendar-alt text-warning me-2"></i> تاريخ الإصدار</span>
                        <span class="detail-val">{{ $document['date'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-clock text-secondary me-2"></i> وقت التدقيق اللحظي</span>
                        <span class="detail-val text-muted small">{{ $document['verified_at'] }}</span>
                    </div>
                </div>

                <div class="row align-items-center pt-2">
                    <div class="col-6 text-center">
                        <div class="official-seal">
                            <i class="fas fa-stamp mb-1"></i>
                            <span>ختم النظام</span>
                            <span style="font-size: 0.65rem;">VERIFIED</span>
                        </div>
                    </div>
                    <div class="col-6 text-center text-muted small">
                        <p class="mb-1 fw-bold text-dark">إدارة الشؤون التعليمية</p>
                        <p class="mb-0 text-success"><i class="fas fa-check-circle me-1"></i> مسجل إلكترونياً</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-center no-print d-flex gap-2 justify-content-center">
                    <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                        <i class="fas fa-print me-1"></i> طباعة الوثيقة
                    </button>
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('تم نسخ رابط التحقق بنجاح!')" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-share-alt me-1"></i> مشاركة رابط التحقق
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="cert-card p-5 text-center">
            <div class="text-danger mb-3">
                <i class="fas fa-triangle-exclamation fa-4x"></i>
            </div>
            <h4 class="fw-bold text-danger mb-2">عذراً، تعذر العثور على الوثيقة المطلوبة</h4>
            <p class="text-muted mb-4">الرقم المرجعي أو التوقيع الرقمي للوثيقة غير مسجل في النظام أو تم إلغاؤه من قبل إدارة المدرسة.</p>
            <a href="/" class="btn btn-dark rounded-pill px-5 fw-bold">العودة للرئيسية</a>
        </div>
    @endif
</div>

</body>
</html>

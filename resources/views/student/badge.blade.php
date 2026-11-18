<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بطاقة الطالب الذكية | {{ $student->name }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Libre+Barcode+39+Text&display=swap" rel="stylesheet">
    <!-- Bootstrap RTL & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #0f172a;
            color: #1e293b;
            min-height: 100vh;
            padding: 40px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .cards-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Standard ID Card Dimensions (CR-80 approx 3.37 x 2.125 inches) */
        .id-card {
            width: 340px;
            height: 520px;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .card-header-accent {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            height: 140px;
            padding: 20px 15px;
            color: white;
            text-align: center;
            position: relative;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .student-photo-wrapper {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            background: #f1f5f9;
            margin: -60px auto 10px;
            position: relative;
            z-index: 2;
        }

        .student-photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .barcode-font {
            font-family: 'Libre Barcode 39 Text', cursive;
            font-size: 2.2rem;
            letter-spacing: 2px;
            color: #0f172a;
            line-height: 1;
        }

        /* Card Back Styles */
        .card-back {
            background: #f8fafc;
        }

        .hologram-strip {
            height: 35px;
            background: linear-gradient(90deg, #94a3b8 0%, #cbd5e1 50%, #94a3b8 100%);
            margin-top: 25px;
            opacity: 0.8;
        }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .id-card { box-shadow: none; border: 1px solid #cbd5e1; page-break-inside: avoid; }
            .cards-container { gap: 20px; }
        }
    </style>
</head>
<body>

    <div class="text-center mb-4 no-print text-white">
        <h3 class="fw-bold"><i class="fas fa-id-card text-info me-2"></i> بطاقة الطالب المدرسية الذكية</h3>
        <p class="text-white-50 small mb-3">بطاقة تعريفية رقمية متوافقة مع أجهزة المسح الذكي للحضور والانصراف والمقصف</p>
        <div class="d-flex gap-2 justify-content-center">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow">
                <i class="fas fa-print me-1"></i> طباعة البطاقة
            </button>
            <a href="javascript:history.back()" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                <i class="fas fa-arrow-right me-1"></i> عودة
            </a>
        </div>
    </div>

    <div class="cards-container">
        
        <!-- CARD FRONT -->
        <div class="id-card">
            <div class="card-header-accent">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <i class="fas fa-graduation-cap fa-lg text-warning"></i>
                    <h6 class="fw-bold m-0">{{ $school->name ?? 'المدرسة الذكية' }}</h6>
                </div>
                <small class="text-white-50" style="font-size: 0.75rem;">بطاقة الهوية المدرسية المعتمدة</small>
            </div>

            <!-- Photo -->
            <div class="student-photo-wrapper">
                @if($student->photo)
                    <img src="{{ asset($student->photo) }}" alt="{{ $student->name }}">
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                        <i class="fas fa-user-graduate fa-3x"></i>
                    </div>
                @endif
            </div>

            <!-- Student Info -->
            <div class="text-center px-3 flex-grow-1">
                <h5 class="fw-bold mb-1 text-dark">{{ $student->name }}</h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 mb-2 fw-bold">
                    {{ $class ? ($class->grade->name ?? '') . ' - ' . $class->name : 'طالب مقيد' }}
                </span>

                <div class="mt-2 text-start bg-light p-2 rounded-3 border small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">رقم القيد:</span>
                        <span class="fw-bold text-dark font-monospace">STD-{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">العام الدراسي:</span>
                        <span class="fw-bold text-dark">{{ $school->academic_year ?? '2026/2027' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">حالة الطالب:</span>
                        <span class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i> منتظم</span>
                    </div>
                </div>

                <!-- QR Code for instant attendance check-in -->
                <div class="mt-3 text-center">
                    <div id="qrcode-front" class="d-inline-block bg-white p-1 border rounded shadow-sm"></div>
                    <div class="text-muted mt-1" style="font-size: 0.68rem;">امسح لتسجيل الحضور الذكي</div>
                </div>
            </div>

            <!-- Bottom Barcode -->
            <div class="bg-light py-2 text-center border-top">
                <div class="barcode-font">*{{ $student->id }}*</div>
            </div>
        </div>

        <!-- CARD BACK -->
        <div class="id-card card-back">
            <div class="hologram-strip"></div>
            
            <div class="p-4 flex-grow-1 d-flex flex-direction-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-center text-dark mb-3 border-bottom pb-2">تعليمات استخدام البطاقة</h6>
                    <ul class="text-muted small ps-3 mb-4" style="line-height: 1.8; font-size: 0.8rem;">
                        <li>هذه البطاقة وثيقة رسمية تصدر عن إدارة المدرسة.</li>
                        <li>يجب إبرازها يومياً عند بوابات الدخول ومقصف المدرسة.</li>
                        <li>لا يجوز استخدام البطاقة من قبل أي شخص آخر غير صاحبها.</li>
                        <li>في حال فقدان البطاقة يرجى مراجعة إدارة شؤون الطلاب فوراً.</li>
                    </ul>

                    <div class="bg-white p-3 rounded-3 border shadow-sm text-center mb-3">
                        <small class="text-muted d-block mb-1">التحقق الرقمي والتوثيق الأمني</small>
                        <div id="qrcode-back" class="d-inline-block p-1 border rounded"></div>
                        <small class="d-block text-primary mt-1 fw-bold" style="font-size: 0.7rem;">مسح رمز الأمان الرسمي</small>
                    </div>
                </div>

                <div class="text-center pt-2 border-top">
                    <p class="mb-0 fw-bold text-dark small">{{ $school->name ?? 'المدرسة الذكية' }}</p>
                    <small class="text-muted" style="font-size: 0.7rem;">هاتف الطوارئ: 920000000 | info@school.com</small>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // QR Front: Checks in attendance (contains student ID format STD-{id})
            new QRCode(document.getElementById("qrcode-front"), {
                text: "{{ $checkinCode }}",
                width: 72,
                height: 72,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });

            // QR Back: Public cryptographic verification URL
            new QRCode(document.getElementById("qrcode-back"), {
                text: "{{ $verificationUrl }}",
                width: 80,
                height: 80,
                colorDark: "#1e3a8a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
</body>
</html>

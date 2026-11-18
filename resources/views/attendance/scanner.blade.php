@extends('layouts.master')

@section('title', 'المسح الذكي للحضور والانصراف')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fas fa-qrcode text-primary me-2"></i> المسح الذكي للحضور والانصراف
            </h3>
            <p class="text-muted small mb-0">وجّه كاميرا الهاتف أو جهاز المسح نحو بطاقة الطالب لتسجيل الحضور الفوري</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold fs-6">
                <i class="fas fa-user-check me-1"></i> الحضور اليوم: <span id="today-counter">{{ $todayCount ?? 0 }}</span>
            </span>
            <a href="{{ route('teacher.attendance.index', 1) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-list me-1"></i> جدول الغياب
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Scanner Viewport Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-camera me-2 text-warning"></i> كاميرا المسح المباشر</span>
                    <div class="d-flex gap-2">
                        <button id="btn-toggle-camera" class="btn btn-sm btn-outline-light rounded-pill px-3">
                            <i class="fas fa-sync-alt me-1"></i> تبديل الكاميرا
                        </button>
                    </div>
                </div>

                <div class="card-body p-4 bg-light text-center">
                    <!-- Scanner Container -->
                    <div id="reader-container" class="position-relative mx-auto rounded-4 overflow-hidden border shadow-sm" style="max-width: 480px; min-height: 320px; background: #000;">
                        <div id="reader" style="width: 100%;"></div>
                        
                        <!-- Scanning Laser Line Overlay -->
                        <div class="scanner-laser" id="scanner-laser"></div>
                    </div>

                    <!-- Manual input fallback -->
                    <div class="mt-4 pt-3 border-top" style="max-width: 480px; margin: 0 auto;">
                        <label class="form-label small fw-bold text-muted d-block text-start">إدخال رقم الطالب يدوياً (في حال تعذر الكاميرا)</label>
                        <form id="manual-scan-form" class="input-group">
                            <input type="text" id="manual-student-id" class="form-control rounded-start-pill" placeholder="مثال: STD-1 أو 1" required>
                            <button type="submit" class="btn btn-primary rounded-end-pill px-4 fw-bold">
                                <i class="fas fa-check me-1"></i> تسجيل
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Feed Stream Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fas fa-stream me-2 text-primary"></i> سجل الحضور اللحظي</span>
                    <span id="scan-status-badge" class="badge bg-secondary rounded-pill">جاهز للمسح</span>
                </div>

                <div class="card-body p-3 overflow-auto" id="recent-scans-list" style="max-height: 520px;">
                    <div id="empty-state" class="text-center py-5 text-muted">
                        <i class="fas fa-id-badge fa-3x mb-3 text-secondary opacity-50"></i>
                        <p class="mb-0">في انتظار أول عملية مسح...</p>
                        <small class="text-muted">ستظهر بطاقات الطلاب هنا فور مسح الرمز</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sound & Camera Script -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    // Web Audio Synthesizer for instant chimes (No external sound files required)
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playBeep(type) {
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);

        if (type === 'success') {
            osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
            osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.3);
        } else {
            osc.frequency.setValueAtTime(220, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.25);
        }
    }

    let isProcessing = false;
    let html5QrCode = null;
    let currentFacingMode = "environment"; // Back camera

    function initScanner() {
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start(
            { facingMode: currentFacingMode },
            config,
            onScanSuccess,
            (errorMessage) => { /* Ignore frame errors */ }
        ).catch(err => {
            console.warn("Camera start warning:", err);
            document.getElementById('scan-status-badge').className = "badge bg-warning text-dark rounded-pill";
            document.getElementById('scan-status-badge').innerText = "الكاميرا غير متاحة، استخدم الإدخال اليدوي";
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;
        
        processCheckIn(decodedText);

        setTimeout(() => {
            isProcessing = false;
        }, 2200); // Debounce scans
    }

    function processCheckIn(code) {
        const badge = document.getElementById('scan-status-badge');
        badge.className = "badge bg-info text-white rounded-pill";
        badge.innerText = "جاري التحقق...";

        fetch("{{ route('attendance.scan_qr') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                playBeep('success');
                badge.className = "badge bg-success rounded-pill";
                badge.innerText = data.already_registered ? "مسجل مسبقاً" : "تم التسجيل بنجاح ✅";

                // Update counter
                if (!data.already_registered) {
                    const counter = document.getElementById('today-counter');
                    counter.innerText = parseInt(counter.innerText || '0') + 1;
                }

                addStudentCardToFeed(data.student, data.already_registered);
            } else {
                playBeep('error');
                badge.className = "badge bg-danger rounded-pill";
                badge.innerText = data.message || "رمز غير صالح";
            }
        })
        .catch(err => {
            playBeep('error');
            badge.className = "badge bg-danger rounded-pill";
            badge.innerText = "خطأ في الاتصال";
        });
    }

    function addStudentCardToFeed(student, alreadyRegistered) {
        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.remove();

        const list = document.getElementById('recent-scans-list');
        const card = document.createElement('div');
        card.className = `p-3 mb-2 rounded-3 border d-flex align-items-center justify-content-between shadow-sm animate__animated animate__fadeInDown ${alreadyRegistered ? 'bg-warning-subtle border-warning-subtle' : 'bg-success-subtle border-success-subtle'}`;

        const photoHtml = student.photo 
            ? `<img src="${student.photo}" class="rounded-circle border" width="46" height="46" style="object-fit: cover;">`
            : `<div class="rounded-circle bg-white border d-flex align-items-center justify-content-center text-primary" style="width:46px;height:46px;"><i class="fas fa-user-graduate"></i></div>`;

        card.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                ${photoHtml}
                <div>
                    <h6 class="fw-bold mb-0 text-dark">${student.name}</h6>
                    <small class="text-muted">${student.class} | <span class="font-monospace">STD-${student.id}</span></small>
                </div>
            </div>
            <div class="text-end">
                <span class="badge ${alreadyRegistered ? 'bg-warning text-dark' : 'bg-success'} rounded-pill mb-1">
                    ${alreadyRegistered ? 'مسجل مسبقاً' : 'حاضر'}
                </span>
                <div class="text-muted font-monospace" style="font-size: 0.75rem;">${student.time}</div>
            </div>
        `;

        list.insertBefore(card, list.firstChild);
    }

    // Manual Form Submit
    document.getElementById('manual-scan-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('manual-student-id');
        if (input.value.trim()) {
            processCheckIn(input.value.trim());
            input.value = '';
        }
    });

    // Toggle Camera Flip
    document.getElementById('btn-toggle-camera').addEventListener('click', function() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                currentFacingMode = (currentFacingMode === "environment") ? "user" : "environment";
                initScanner();
            });
        }
    });

    document.addEventListener('DOMContentLoaded', initScanner);
</script>

<style>
    .scanner-laser {
        position: absolute;
        top: 20%;
        left: 5%;
        width: 90%;
        height: 3px;
        background: #0284c7;
        box-shadow: 0 0 15px #38bdf8;
        border-radius: 50%;
        animation: laserScan 2s infinite ease-in-out;
        pointer-events: none;
        z-index: 10;
    }
    @keyframes laserScan {
        0%, 100% { top: 15%; opacity: 0.4; }
        50% { top: 85%; opacity: 1; }
    }
</style>
@endsection

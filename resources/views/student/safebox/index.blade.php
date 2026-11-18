@extends('layouts.student')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #1f4037, #99f2c8); color: #fff;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-user-shield me-1"></i> بيئة مدرسية آمنة وسرية
            </span>
            <h2 class="fw-bold mb-1 text-white">صندوق الأمان المدرسي ومكافحة التنمر 🛡️</h2>
            <p class="mb-0 text-white-50">
                صوتك مسموع وسرك في أمان تام. يمكنك مراسلة المرشد الطلابي والإدارة بسرية كاملة دون كشف هويتك.
            </p>
        </div>
        <div class="d-none d-md-block text-white opacity-25">
            <i class="fas fa-shield-heart fa-4x"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- القسم الأول: نبض المشاعر الأسبوعي --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="text-center mb-3">
                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-heart-pulse text-danger me-2"></i> كيف تشعر هذا الأسبوع؟</h5>
                <small class="text-muted">نسعى دائماً لأن تكون مدرستنا مكاناً مريحاً وسعيداً لك</small>
            </div>

            @if($thisWeekPulse)
                <div class="text-center py-4 bg-light rounded-4 border my-auto">
                    <span class="display-3 d-block mb-2">{{ $thisWeekPulse->emoji }}</span>
                    <h6 class="fw-bold text-success mb-1">تم تسجيل شعورك لهذا الأسبوع!</h6>
                    <span class="badge bg-success-subtle text-success px-3 py-1">
                        {{ $thisWeekPulse->mood === 'great' ? 'رائع ومتحمس' : ($thisWeekPulse->mood === 'calm' ? 'هادئ ومستقر' : ($thisWeekPulse->mood === 'anxious' ? 'قلق بعض الشيء' : ($thisWeekPulse->mood === 'sad' ? 'حزين' : 'أشعر بالضغط'))) }}
                    </span>
                    @if($thisWeekPulse->note)
                        <p class="text-muted small mt-3 px-3 mb-0 fst-italic">"{{ $thisWeekPulse->note }}"</p>
                    @endif
                    <small class="text-muted d-block mt-3" style="font-size: 0.75rem;">يمكنك التحديث في أي وقت إن تغير شعورك</small>
                </div>
            @endif

            <form action="{{ route('student.safebox.pulse') }}" method="POST" class="mt-3">
                @csrf
                <label class="form-label fw-bold text-secondary small d-block text-center mb-2">اختر الإيموجي الأقرب لحالتك:</label>
                <div class="d-flex justify-content-between gap-1 mb-3">
                    <label class="btn btn-outline-light border flex-fill text-center p-2 rounded-3 text-dark">
                        <input type="radio" name="mood" value="great" class="d-none" required {{ ($thisWeekPulse && $thisWeekPulse->mood === 'great') ? 'checked' : '' }}>
                        <span class="fs-3 d-block">😃</span>
                        <small style="font-size: 0.7rem;">ممتاز</small>
                    </label>
                    <label class="btn btn-outline-light border flex-fill text-center p-2 rounded-3 text-dark">
                        <input type="radio" name="mood" value="calm" class="d-none" {{ ($thisWeekPulse && $thisWeekPulse->mood === 'calm') ? 'checked' : '' }}>
                        <span class="fs-3 d-block">😌</span>
                        <small style="font-size: 0.7rem;">هادئ</small>
                    </label>
                    <label class="btn btn-outline-light border flex-fill text-center p-2 rounded-3 text-dark">
                        <input type="radio" name="mood" value="anxious" class="d-none" {{ ($thisWeekPulse && $thisWeekPulse->mood === 'anxious') ? 'checked' : '' }}>
                        <span class="fs-3 d-block">😟</span>
                        <small style="font-size: 0.7rem;">قلق</small>
                    </label>
                    <label class="btn btn-outline-light border flex-fill text-center p-2 rounded-3 text-dark">
                        <input type="radio" name="mood" value="sad" class="d-none" {{ ($thisWeekPulse && $thisWeekPulse->mood === 'sad') ? 'checked' : '' }}>
                        <span class="fs-3 d-block">😢</span>
                        <small style="font-size: 0.7rem;">حزين</small>
                    </label>
                    <label class="btn btn-outline-light border flex-fill text-center p-2 rounded-3 text-dark">
                        <input type="radio" name="mood" value="stressed" class="d-none" {{ ($thisWeekPulse && $thisWeekPulse->mood === 'stressed') ? 'checked' : '' }}>
                        <span class="fs-3 d-block">😫</span>
                        <small style="font-size: 0.7rem;">مضغوط</small>
                    </label>
                </div>

                <div class="mb-3">
                    <textarea name="note" rows="2" class="form-control text-end small" placeholder="هل تحب مشاركة ما يضايقك أو يسعدك؟ (اختياري)">{{ $thisWeekPulse->note ?? '' }}</textarea>
                </div>

                <button type="submit" class="btn btn-outline-success w-100 fw-bold shadow-sm">
                    <i class="fas fa-check me-1"></i> تثبيت شعوري
                </button>
            </form>
        </div>
    </div>

    {{-- القسم الثاني: نموذج إرسال بلاغ سري --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white mb-4">
            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-paper-plane text-primary me-2"></i> إرسال بلاغ أو استشارة سرية</h5>
            <p class="text-muted small mb-3">
                يمكنك التبليغ عن أي حالة تنمر تعرضت لها أو شهدتها، أو طلب مساعدة من المرشد الطلابي.
            </p>

            <form action="{{ route('student.safebox.report') }}" method="POST">
                @csrf

                <div class="alert alert-info border-0 d-flex align-items-center justify-content-between p-3 rounded-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-secret fa-2x text-primary"></i>
                        <div>
                            <strong class="d-block text-dark">خيار الهوية المجهولة (Anonymous):</strong>
                            <small class="text-secondary">لن يتم حفظ اسمك أو بريدك الإلكتروني في البلاغ مطلقاً.</small>
                        </div>
                    </div>
                    <div class="form-check form-switch fs-4 m-0">
                        <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="isAnonymousSwitch" checked>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تصنيف الحالة:</label>
                        <select name="category" class="form-select" required>
                            <option value="bullying">🚨 تعرض لتنمر لفظي أو جسدي</option>
                            <option value="mental_stress">😟 قلق وضغط نفسي حاد</option>
                            <option value="academic_pressure">📚 تعثر وضغط دراسي شديد</option>
                            <option value="family_issue">🏠 مشكلة أسرية أو خاصة</option>
                            <option value="safety_hazard">⚠️ ملاحظة أمن وسلامة في المدرسة</option>
                            <option value="other">💬 استشارة خاصة أخرى</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">مستوى الأهمية والسرعة المطلوبة:</label>
                        <select name="urgency" class="form-select" required>
                            <option value="normal">عادي (متابعة خلال أيام)</option>
                            <option value="high">هام (يرجى الرد قريباً)</option>
                            <option value="critical">🚨 حرج وطارئ جداً (يحتاج تدخل فوري)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">موضوع البلاغ باختصار:</label>
                    <input type="text" name="title" class="form-control" placeholder="مثال: تعرض لمضايقات مستمرة أثناء حصة الرياضة" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">تفاصيل ما حدث:</label>
                    <textarea name="description" rows="5" class="form-control" placeholder="اكتب كل ما تريد بثقة وسرية تامة..." required></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                        <i class="fas fa-lock me-1"></i> إرسال البلاغ في الصندوق الآمن
                    </button>
                    <span class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i> مشفر ومحمي بلوائح حماية الخصوصية</span>
                </div>
            </form>
        </div>

        {{-- القسم الثالث: متابعة البلاغات برمز التتبع --}}
        <div class="card border-0 shadow-sm p-4 bg-white">
            <h5 class="fw-bold text-dark mb-2"><i class="fas fa-key text-warning me-2"></i> متابعة رد المرشد الطلابي برمز التتبع السري</h5>
            <p class="text-muted small mb-3">أدخل الرمز السري الذي استلمته عند إرسال البلاغ لمراجعة رد المرشد بدون تسجيل دخول أو كشف هويتك:</p>

            <form action="{{ route('student.safebox.index') }}" method="GET" class="d-flex gap-2 mb-3">
                <input type="text" name="token" class="form-control font-monospace" placeholder="مثال: SAFE-XXXXXX" value="{{ request('token') }}" required>
                <button type="submit" class="btn btn-warning fw-bold text-dark px-4">
                    <i class="fas fa-search me-1"></i> فحص الرد
                </button>
            </form>

            @if(request()->has('token'))
                @if($trackedReport)
                    <div class="p-3 bg-light rounded-3 border mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary font-monospace">{{ $trackedReport->token }}</span>
                            @if($trackedReport->status === 'resolved')
                                <span class="badge bg-success px-3 py-1">✓ تم الحل والمعالجة</span>
                            @elseif($trackedReport->status === 'in_review')
                                <span class="badge bg-warning text-dark px-3 py-1">قيد المتابعة والإجراء</span>
                            @else
                                <span class="badge bg-info px-3 py-1">وصل للمرشد الطلابي</span>
                            @endif
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $trackedReport->title }}</h6>
                        <p class="text-muted small mb-3">{{ $trackedReport->description }}</p>

                        <div class="p-3 bg-white rounded border-start border-4 border-success">
                            <strong class="text-success d-block mb-1"><i class="fas fa-reply me-1"></i> رد وتوجيه المرشد الطلابي:</strong>
                            @if($trackedReport->counselor_response)
                                <p class="text-dark small mb-0">{{ $trackedReport->counselor_response }}</p>
                                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">تاريخ الرد: {{ $trackedReport->responded_at ? $trackedReport->responded_at->format('Y-m-d H:i') : '' }}</small>
                            @else
                                <p class="text-muted small mb-0 fst-italic">البلاغ مسجل وتحت المراجعة السرية حالياً، سيتم الرد عليك قريباً هنا بنفس الرمز.</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning border-0 small mt-2">
                        لم يتم العثور على بلاغ مطابق للرمز المدخل ({{ request('token') }}). يرجى التأكد من الرمز.
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@endsection

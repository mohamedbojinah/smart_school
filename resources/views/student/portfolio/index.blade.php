@extends('layouts.student')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #4b6cb7, #182848); color: #fff;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-star me-1"></i> هويتك الرقمية وسيرتك الموثقة
            </span>
            <h2 class="fw-bold mb-1 text-white">سجل الإنجاز الرقمي الموثق للطالب (Digital Portfolio) 🌟</h2>
            <p class="mb-0 text-white-50">
                ابنِ سيرتك الذاتية المدرسية، وثّق مشاريعك، شهاداتك، وساعات التطوع المعتمدة رسمياً من إدارة المدرسة.
            </p>
        </div>
        <div>
            <a href="{{ route('portfolio.public', $portfolio->slug) }}" class="btn btn-warning fw-bold text-dark shadow" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i> معاينة البورتفوليو العام
            </a>
        </div>
    </div>
</div>

{{-- بطاقة الرابط العام والمشاركة --}}
<div class="alert alert-light border shadow-sm p-3 rounded-4 d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-primary text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="fas fa-globe"></i>
        </div>
        <div>
            <span class="fw-bold text-dark d-block">رابط ملفك الرقمي الموثق (Public Portfolio URL):</span>
            <a href="{{ route('portfolio.public', $portfolio->slug) }}" target="_blank" class="text-primary text-break small">
                {{ route('portfolio.public', $portfolio->slug) }}
            </a>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success px-3 py-2">
            <i class="fas fa-shield-check me-1"></i> معتمد برقم: {{ $portfolio->verification_seal_number }}
        </span>
    </div>
</div>

<div class="row g-4">
    {{-- بيانات البروفايل الأساسية --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-id-card text-primary me-2"></i> البيانات الشخصية والنبذة</h5>

            <form action="{{ route('student.portfolio.profile.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">العنوان التعريفي (Headline):</label>
                    <input type="text" name="headline" class="form-control" value="{{ $portfolio->headline }}" placeholder="مثال: طالب موهوب وشغوف بالبرمجة والروبوتات" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">نبذة عني وطموحاتي (Bio):</label>
                    <textarea name="about_me" rows="4" class="form-control" placeholder="اكتب نبذة عن اهتماماتك وطموحاتك الأكاديمية والمهنية...">{{ $portfolio->about_me }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">المهارات والقدرات (مفصولة بفاصلة):</label>
                    <input type="text" name="skills" class="form-control" value="{{ $portfolio->skills }}" placeholder="مثال: حل المشكلات, Python, الخطابة, العمل الجماعي">
                    <small class="text-muted">ستظهر كشارات مهارية أنيقة في صفحتك العامة.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ساعات العمل التطوعي المعتمدة:</label>
                    <div class="input-group">
                        <input type="number" name="volunteer_hours" class="form-control" min="0" max="1000" value="{{ $portfolio->volunteer_hours }}">
                        <span class="input-group-text bg-light">ساعة خدمة مجتمعية</span>
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicSwitch" {{ $portfolio->is_public ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="isPublicSwitch">إتاحة الملف العام للجامعات والمعارض</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                    <i class="fas fa-save me-1"></i> حفظ وتحديث البيانات
                </button>
            </form>
        </div>
    </div>

    {{-- المشاريع والإنجازات الموثقة --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-trophy text-warning me-2"></i> الإنجازات والمشاريع الموثقة ({{ $portfolio->items->count() }})</h5>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus me-1"></i> إضافة إنجاز جديد
                </button>
            </div>

            <div class="list-group list-group-flush">
                @forelse($portfolio->items as $item)
                <div class="list-group-item p-3 border rounded-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @if($item->category === 'project')
                                <span class="badge bg-primary px-2 py-1"><i class="fas fa-laptop-code me-1"></i> مشروع علمي / ابتكار</span>
                            @elseif($item->category === 'certificate')
                                <span class="badge bg-success px-2 py-1"><i class="fas fa-certificate me-1"></i> شهادة / تكريم</span>
                            @elseif($item->category === 'competition')
                                <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-medal me-1"></i> أولمبياد / مسابقة</span>
                            @elseif($item->category === 'research')
                                <span class="badge bg-info text-dark px-2 py-1"><i class="fas fa-microscope me-1"></i> بحث ومقال</span>
                            @else
                                <span class="badge bg-secondary px-2 py-1"><i class="fas fa-palette me-1"></i> فن أو رياضة</span>
                            @endif
                            <span class="badge bg-light text-dark border ms-2">{{ $item->year }}</span>
                        </div>
                        <form action="{{ route('student.portfolio.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإنجاز؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="حذف">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>

                    <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                    <p class="text-secondary small mb-2">{{ $item->description }}</p>

                    @if($item->project_url)
                        <a href="{{ $item->project_url }}" target="_blank" class="small text-primary text-decoration-none">
                            <i class="fas fa-link me-1"></i> معاينة رابط المشروع الخارجي
                        </a>
                    @endif
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                    لم تضف أي مشاريع بعد. اضغط على "إضافة إنجاز جديد" لتوثيق ابتكاراتك ومشاركاتك المدرسية!
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- مودال إضافة إنجاز جديد --}}
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start">
            <form action="{{ route('student.portfolio.items.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fas fa-plus-circle text-primary me-1"></i> إضافة مشروع أو شهادة إلى سجلك
                    </h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-end p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان الإنجاز أو المشروع:</label>
                        <input type="text" name="title" class="form-control text-end" placeholder="مثال: نظام روبوت فرز النفايات الذكي" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold">تصنيف الإنجاز:</label>
                            <select name="category" class="form-select text-end" required>
                                <option value="project">مشروع علمي / برمجي / ابتكار</option>
                                <option value="certificate">شهادة تميز أو دورة تدريبية</option>
                                <option value="competition">مشاركة في أولمبياد أو مسابقة</option>
                                <option value="research">بحث علمي أو ورقة عمل</option>
                                <option value="art_or_sport">نشاط فني، أدبي، أو رياضي</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">السنة:</label>
                            <input type="text" name="year" class="form-control text-end" value="{{ date('Y') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">رابط المشروع أو النموذج (اختياري):</label>
                        <input type="url" name="project_url" class="form-control text-start font-monospace" placeholder="https://github.com/... أو رابط خارجي">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">وصف الإنجاز ودورك فيه:</label>
                        <textarea name="description" rows="4" class="form-control text-end" placeholder="اشرح فكرة المشروع، والتقنيات المستخدمة، والنتائج التي حققتها..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-check me-1"></i> إضافة وتثبيت في السجل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

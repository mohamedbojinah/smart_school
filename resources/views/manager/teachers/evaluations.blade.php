@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-star me-1"></i> الكادر التعليمي
            </span>
            <h2 class="fw-bold mb-1 text-white">منظومة تقييم أداء المعلمين ⭐</h2>
            <p class="mb-0 opacity-75 text-white">
                تقييم الأداء السنوي، قياس جودة التدريس، وإصدار شهادات التقدير وخطابات التوجيه.
            </p>
        </div>
        <a href="{{ route('manager.letters.create') }}" class="btn btn-warning fw-bold text-dark shadow-sm">
            <i class="fas fa-certificate me-1"></i> إصدار خطاب رسمي جديد
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>المعلم</th>
                    <th>المواد والمراحل</th>
                    <th>آخر تقييم مسجل</th>
                    <th>المواظبة</th>
                    <th>جودة الشرح</th>
                    <th>التعاون</th>
                    <th>التوصية الإدارية</th>
                    <th class="text-center">إجراءات المدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                @php
                    $latestEval = $teacher->teacherEvaluations->first();
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px;">
                                <i class="fas fa-chalkboard-teacher fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $teacher->name }}</h6>
                                <small class="text-muted">{{ $teacher->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($teacher->teachingSubjects->isNotEmpty())
                            @foreach($teacher->teachingSubjects->take(2) as $subj)
                                <span class="badge bg-light text-dark border">{{ $subj->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">غير محدد</span>
                        @endif
                    </td>
                    <td>
                        @if($latestEval)
                            <div class="d-flex align-items-center gap-1 text-warning fw-bold">
                                <span>{{ $latestEval->overall_rating }}</span>
                                <i class="fas fa-star"></i>
                                <small class="text-muted ms-1">({{ $latestEval->created_at->format('Y-m-d') }})</small>
                            </div>
                        @else
                            <span class="badge bg-secondary-subtle text-muted">لم يُقيّم بعد</span>
                        @endif
                    </td>
                    <td>
                        {{ $latestEval ? $latestEval->punctuality_rating . '/5' : '-' }}
                    </td>
                    <td>
                        {{ $latestEval ? $latestEval->teaching_quality_rating . '/5' : '-' }}
                    </td>
                    <td>
                        {{ $latestEval ? $latestEval->cooperation_rating . '/5' : '-' }}
                    </td>
                    <td>
                        @if($latestEval && $latestEval->recommendation)
                            <span class="badge bg-success-subtle text-success border border-success">{{ $latestEval->recommendation }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2">
                            {{-- زر التقييم يفتح المودال --}}
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#evalModal{{ $teacher->id }}">
                                <i class="fas fa-edit me-1"></i> تقييم
                            </button>

                            {{-- زر إصدار خطاب --}}
                            <a href="{{ route('manager.letters.create', ['teacher' => $teacher->id]) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-envelope-open-text me-1"></i> خطاب
                            </a>
                        </div>

                        {{-- مودال التقييم --}}
                        <div class="modal fade" id="evalModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true" dir="rtl">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content text-start">
                                    <form action="{{ route('manager.evaluations.store', $teacher->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold text-dark">
                                                <i class="fas fa-star text-warning me-1"></i> تقييم أداء: {{ $teacher->name }}
                                            </h5>
                                            <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4 text-end">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">الانضباط والمواظبة في الحضور (1 - 5):</label>
                                                <select name="punctuality_rating" class="form-select text-end" required>
                                                    <option value="5">5 - ممتاز ملتزم جداً</option>
                                                    <option value="4">4 - جيد جداً</option>
                                                    <option value="3">3 - متوسط (يحتاج متابعة)</option>
                                                    <option value="2">2 - ضعيف</option>
                                                    <option value="1">1 - غير منضبط</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">جودة الشرح والتمكن من المادة (1 - 5):</label>
                                                <select name="teaching_quality_rating" class="form-select text-end" required>
                                                    <option value="5">5 - تمكن رائع وأساليب حديثة</option>
                                                    <option value="4">4 - جيد جداً</option>
                                                    <option value="3">3 - متوسط</option>
                                                    <option value="2">2 - بحاجة لورش تدريبية</option>
                                                    <option value="1">1 - ضعيف</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">التعاون مع الإدارة والزملاء (1 - 5):</label>
                                                <select name="cooperation_rating" class="form-select text-end" required>
                                                    <option value="5">5 - متعاون ومبادر دائماً</option>
                                                    <option value="4">4 - جيد جداً</option>
                                                    <option value="3">3 - مقبول</option>
                                                    <option value="2">2 - يرفض بعض المهام</option>
                                                    <option value="1">1 - غير متعاون</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">التوصية الإدارية المقترحة:</label>
                                                <input type="text" name="recommendation" class="form-control text-end" placeholder="مثال: صرف مكافأة تميز، توجيه خطاب شكر، حضور دورة تأهيلية" value="{{ $latestEval->recommendation ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">ملاحظات المدير السرية:</label>
                                                <textarea name="notes" rows="3" class="form-control text-end" placeholder="أي ملاحظات إشرافية خاصة">{{ $latestEval->notes ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light d-flex justify-content-between">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-primary fw-bold">
                                                <i class="fas fa-save me-1"></i> حفظ وتثبيت التقييم
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
                    <td colspan="8" class="text-center py-4 text-muted">لا يوجد معلمون مسجلون في المدرسة حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $teachers->links() }}
    </div>
</div>

@endsection

@extends('layouts.teacher')

@section('title', 'مساعد الذكاء الاصطناعي لإعداد الاختبارات')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fas fa-wand-magic-sparkles text-primary me-2"></i> مساعد الذكاء الاصطناعي لإعداد الاختبارات
            </h3>
            <p class="text-muted small mb-0">قم بتوليد أسئلة اختيار من متعدد متوافقة مع المناهج التعليمية بضغطة زر وحفظها فورياً في بنك الاختبارات</p>
        </div>
        <div>
            <a href="{{ route('teacher.online_exams.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-right me-1"></i> قائمة الاختبارات
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- AI Input Form Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-gradient text-white p-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
                    <span class="fw-bold"><i class="fas fa-sliders me-2 text-warning"></i> إعدادات التوليد الذكي</span>
                </div>
                <div class="card-body p-4">
                    <form id="ai-generator-form">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">المادة الدراسية</label>
                            <select id="subject_name" class="form-select rounded-3" required>
                                <option value="">-- اختر المادة --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->name }}">{{ $sub->name }} ({{ $sub->grade->name ?? 'عام' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">موضوع الدرس أو الوحدة التعليمية</label>
                            <input type="text" id="topic" class="form-control rounded-3" placeholder="مثال: خصائص المادة والضغط الجوي" required>
                            <small class="text-muted" style="font-size: 0.75rem;">يمكنك كتابة عنوان الدرس أو أهداف التعلم المحددة</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark small">عدد الأسئلة</label>
                                <select id="count" class="form-select rounded-3">
                                    <option value="3">3 أسئلة</option>
                                    <option value="5" selected>5 أسئلة</option>
                                    <option value="8">8 أسئلة</option>
                                    <option value="10">10 أسئلة</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark small">مستوى الصعوبة</label>
                                <select id="difficulty" class="form-select rounded-3">
                                    <option value="easy">سهل (مفاهيم)</option>
                                    <option value="medium" selected>متوسط (تطبيقي)</option>
                                    <option value="hard">متقدم (تحليلي)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="btn-generate" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <i class="fas fa-bolt me-1"></i> توليد الأسئلة بالذكاء الاصطناعي
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generated Questions Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="fw-bold text-dark"><i class="fas fa-list-check me-2 text-primary"></i> الأسئلة المقترحة</span>
                    
                    <div id="save-actions" class="d-none d-flex gap-2 align-items-center">
                        <select id="target-exam-id" class="form-select form-select-sm rounded-pill" style="min-width: 200px;">
                            <option value="">-- اختر اختبار لحفظ الأسئلة فيه --</option>
                            @foreach($onlineExams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }} ({{ $exam->class_id }})</option>
                            @endforeach
                        </select>
                        <button id="btn-save-all" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                            <i class="fas fa-save me-1"></i> حفظ في الاختبار
                        </button>
                    </div>
                </div>

                <div class="card-body p-4" id="questions-container">
                    <div id="ai-placeholder" class="text-center py-5 text-muted">
                        <div class="mb-3">
                            <i class="fas fa-robot fa-4x text-primary opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark">جاهز لصياغة أسئلتك بذكاء</h5>
                        <p class="text-muted small mb-0">حدد المادة وعنوان الدرس من القائمة الجانبية واضغط على "توليد الأسئلة"</p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="ai-loading" class="d-none text-center py-5">
                        <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                        <h6 class="fw-bold text-dark">جاري تحليل الدرس وصياغة الأسئلة الذكية...</h6>
                        <small class="text-muted">نطبق معايير التقييم التعليمية لضمان دقة الخيارات والإجابات</small>
                    </div>

                    <!-- Questions List will be dynamically inserted here -->
                    <div id="questions-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let generatedQuestionsData = [];

    document.getElementById('ai-generator-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const subjectName = document.getElementById('subject_name').value;
        const topic = document.getElementById('topic').value;
        const count = document.getElementById('count').value;
        const difficulty = document.getElementById('difficulty').value;

        const placeholder = document.getElementById('ai-placeholder');
        const loading = document.getElementById('ai-loading');
        const list = document.getElementById('questions-list');
        const saveActions = document.getElementById('save-actions');
        const btnGen = document.getElementById('btn-generate');

        placeholder.classList.add('d-none');
        list.innerHTML = '';
        saveActions.classList.add('d-none');
        loading.classList.remove('d-none');
        btnGen.disabled = true;

        fetch("{{ route('teacher.ai.generate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject_name: subjectName,
                topic: topic,
                count: count,
                difficulty: difficulty
            })
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('d-none');
            btnGen.disabled = false;

            if (data.success && data.questions.length > 0) {
                generatedQuestionsData = data.questions;
                renderQuestions(data.questions);
                saveActions.classList.remove('d-none');
            } else {
                list.innerHTML = `<div class="alert alert-warning text-center">تعذر توليد الأسئلة، يرجى المحاولة بعنوان درس آخر.</div>`;
            }
        })
        .catch(err => {
            loading.classList.add('d-none');
            btnGen.disabled = false;
            list.innerHTML = `<div class="alert alert-danger text-center">حدث خطأ أثناء الاتصال بالخادم.</div>`;
        });
    });

    function renderQuestions(questions) {
        const list = document.getElementById('questions-list');
        list.innerHTML = '';

        questions.forEach((q, idx) => {
            const card = document.createElement('div');
            card.className = "card border rounded-3 mb-3 p-3 shadow-sm bg-light";

            let optionsHtml = '';
            if (Array.isArray(q.options)) {
                q.options.forEach((opt, oIdx) => {
                    const isCorrect = (opt === q.correct_answer);
                    optionsHtml += `
                        <div class="col-md-6 mb-2">
                            <div class="p-2 rounded border small d-flex justify-content-between align-items-center ${isCorrect ? 'bg-success-subtle border-success text-success fw-bold' : 'bg-white text-dark'}">
                                <span><i class="fas ${isCorrect ? 'fa-check-circle text-success' : 'fa-circle text-muted'} me-2"></i> ${opt}</span>
                                ${isCorrect ? '<span class="badge bg-success rounded-pill" style="font-size:0.65rem;">صحيحة</span>' : ''}
                            </div>
                        </div>
                    `;
                });
            }

            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary rounded-pill px-3 py-1">سؤال ${idx + 1}</span>
                    <span class="badge bg-secondary-subtle text-dark border">درجة واحدة</span>
                </div>
                <h6 class="fw-bold text-dark mb-3">${q.question_text}</h6>
                <div class="row g-2 mb-2">
                    ${optionsHtml}
                </div>
                ${q.explanation ? `<small class="text-muted d-block mt-2 border-top pt-2"><i class="fas fa-lightbulb text-warning me-1"></i> <strong>الشرح:</strong> ${q.explanation}</small>` : ''}
            `;

            list.appendChild(card);
        });
    }

    // Save questions to exam
    document.getElementById('btn-save-all').addEventListener('click', function() {
        const examId = document.getElementById('target-exam-id').value;
        if (!examId) {
            alert('يرجى اختيار الاختبار أولاً لحفظ الأسئلة فيه.');
            return;
        }

        if (generatedQuestionsData.length === 0) {
            alert('لا توجد أسئلة لحفظها.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerText = 'جاري الحفظ...';

        fetch("{{ route('teacher.ai.save_to_exam') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                online_exam_id: examId,
                questions: generatedQuestionsData
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> حفظ في الاختبار';
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                alert(data.message || 'حدث خطأ أثناء الحفظ.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> حفظ في الاختبار';
            alert('خطأ في الاتصال بالخادم.');
        });
    });
</script>
@endsection

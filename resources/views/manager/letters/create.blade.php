@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #134e5e, #71b280);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">تحرير خطاب رسمي ✍️</h2>
            <p class="mb-0 opacity-75 text-white">إصدار مكاتبة رسمية معتمدة من مدير المدرسة برقم قيد وختم إلكتروني.</p>
        </div>
        <a href="{{ route('manager.letters.index') }}" class="btn btn-outline-light">
            <i class="fas fa-arrow-right me-1"></i> العودة للأرشيف
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('manager.letters.store') }}" method="POST">
                @csrf

                {{-- اختيار المستلم --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">الموجّه إليه الخطاب (المعلم أو الموظف):</label>
                    <select name="recipient_id" class="form-select" required>
                        <option value="">-- اختر العضو المستهدف --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ (isset($selectedTeacher) && $selectedTeacher->id == $t->id) ? 'selected' : '' }}>
                                {{ $t->name }} ({{ $t->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- نوع الخطاب --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">تصنيف الخطاب:</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeAppreciation" value="appreciation" checked onchange="applyTemplate('appreciation')">
                            <label class="form-check-label fw-bold text-success" for="typeAppreciation">
                                <i class="fas fa-award me-1"></i> خطاب شكر وتقدير
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeWarning" value="warning" onchange="applyTemplate('warning')">
                            <label class="form-check-label fw-bold text-danger" for="typeWarning">
                                <i class="fas fa-exclamation-triangle me-1"></i> خطاب لفت نظر رسمي
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeGuidance" value="guidance" onchange="applyTemplate('guidance')">
                            <label class="form-check-label fw-bold text-info" for="typeGuidance">
                                <i class="fas fa-info-circle me-1"></i> خطاب توجيه وإرشاد
                            </label>
                        </div>
                    </div>
                </div>

                {{-- عنوان الخطاب --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">موضوع الخطاب:</label>
                    <input type="text" name="title" id="letterTitle" class="form-control" required 
                           value="شكر وتقدير على الأداء المتميز والجهود التعليمية المبذولة">
                </div>

                {{-- نص الخطاب --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">نص المكاتبة الرسمية:</label>
                    <textarea name="content" id="letterContent" rows="7" class="form-control" required style="font-size: 1.05rem; line-height: 1.8;">يطيب لإدارة المدرسة أن تتقدم لكم بوافر الشكر والتقدير لجهودكم المخلصة وأدائكم المتميز خلال الفصل الدراسي، وما لمسناه من أثر إيجابي وانضباط عالٍ انعكس على تفوق طلابكم وتميزهم. سائلين الله لكم دوام التوفيق والنجاح والعطاء المستمر.</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                        <i class="fas fa-check-double me-1"></i> اعتماد وإصدار الخطاب الرسمي
                    </button>
                    <span class="text-muted small">سيتم إنشاء رقم قيد فريد وختم إلكتروني تلقائياً فور الحفظ.</span>
                </div>
            </form>
        </div>

        {{-- القوالب الجاهزة السريعة --}}
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-magic text-warning me-2"></i> نماذج جاهزة وسريعة</h6>
                
                <button type="button" class="btn btn-outline-success w-100 mb-2 text-start" onclick="applyTemplate('appreciation')">
                    <i class="fas fa-award me-1"></i> نموذج شهادة شكر وتميز
                </button>

                <button type="button" class="btn btn-outline-danger w-100 mb-2 text-start" onclick="applyTemplate('warning')">
                    <i class="fas fa-exclamation-circle me-1"></i> نموذج لفت نظر للتأخر أو التقصير
                </button>

                <button type="button" class="btn btn-outline-info w-100 text-start" onclick="applyTemplate('guidance')">
                    <i class="fas fa-info-circle me-1"></i> نموذج توجيه لتطوير الاختبارات
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function applyTemplate(type) {
    const titleInput = document.getElementById('letterTitle');
    const contentInput = document.getElementById('letterContent');

    if (type === 'appreciation') {
        document.getElementById('typeAppreciation').checked = true;
        titleInput.value = 'شكر وتقدير على الأداء المتميز والجهود التعليمية المبذولة';
        contentInput.value = 'يطيب لإدارة المدرسة أن تتقدم لكم بوافر الشكر والتقدير لجهودكم المخلصة وأدائكم المتميز خلال الفصل الدراسي، وما لمسناه من أثر إيجابي وانضباط عالٍ انعكس على تفوق طلابكم وتميزهم. سائلين الله لكم دوام التوفيق والنجاح والعطاء المستمر.';
    } else if (type === 'warning') {
        document.getElementById('typeWarning').checked = true;
        titleInput.value = 'لفت نظر رسمي بشأن الالتزام بالمواعيد واللوائح المدرسية';
        contentInput.value = 'نود إحاطتكم علماً بأنه قد لوحظ مؤخراً تكرار التأخر عن مواعيد الحصص المقررة / التأخر في رصد الدرجات في الوقت المحدد، وعليه نرجو منكم مراعاة اللوائح والتعليمات المتبعة مستقبلاً حرصاً على مصلحة الطلاب والمسيرة التعليمية.';
    } else if (type === 'guidance') {
        document.getElementById('typeGuidance').checked = true;
        titleInput.value = 'توجيه إداري وفني بشأن خطة التحسين وبنك الأسئلة';
        contentInput.value = 'إشارة إلى نتائج التقييم الأكاديمي الدوري لطلاب الفصول المسندة إليكم، نأمل العمل على تفعيل بنك الأسئلة الإلكتروني وتكثيف المراجعات للطلاب ذوي التحصيل الحرج وفق خطة المعالجة المعتمدة.';
    }
}
</script>

@endsection

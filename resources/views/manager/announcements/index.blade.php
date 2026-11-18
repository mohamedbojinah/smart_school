@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #e52d27, #b31217);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-bullhorn me-1"></i> الاتصال المؤسسي
            </span>
            <h2 class="fw-bold mb-1 text-white">مركز التعاميم المدرسية وبلاغات الطوارئ 📢</h2>
            <p class="mb-0 opacity-75 text-white">
                بث القرارات الإدارية، إشعارات العطل المدرسية، ونداءات الطوارئ الفورية لجميع أطراف المنظومة.
            </p>
        </div>
        <button type="button" class="btn btn-light fw-bold text-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
            <i class="fas fa-plus me-1"></i> إنشاء تعميم أو بلاغ طوارئ
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>عنوان التعميم / البلاغ</th>
                    <th>الفئة المستهدفة</th>
                    <th>مستوى الأهمية</th>
                    <th>الحالة</th>
                    <th>تاريخ النشر</th>
                    <th>الناشر</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $ann)
                <tr class="{{ $ann->priority === 'emergency' ? 'table-danger' : '' }}">
                    <td>
                        <strong class="text-dark d-block">{{ $ann->title }}</strong>
                        <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">
                            {{ Str::limit($ann->content, 85) }}
                        </small>
                    </td>
                    <td>
                        @if($ann->target_role === 'all')
                            <span class="badge bg-primary px-2 py-1"><i class="fas fa-users me-1"></i> الجميع</span>
                        @elseif($ann->target_role === 'teacher')
                            <span class="badge bg-info text-dark px-2 py-1"><i class="fas fa-chalkboard-teacher me-1"></i> المعلمين</span>
                        @elseif($ann->target_role === 'student')
                            <span class="badge bg-secondary px-2 py-1"><i class="fas fa-user-graduate me-1"></i> الطلاب</span>
                        @elseif($ann->target_role === 'parent')
                            <span class="badge bg-success px-2 py-1"><i class="fas fa-user-friends me-1"></i> أولياء الأمور</span>
                        @else
                            <span class="badge bg-warning text-dark px-2 py-1">الإداريين</span>
                        @endif
                    </td>
                    <td>
                        @if($ann->priority === 'emergency')
                            <span class="badge bg-danger px-2 py-1">
                                <i class="fas fa-exclamation-triangle me-1"></i> طوارئ عاجل 🚨
                            </span>
                        @elseif($ann->priority === 'high')
                            <span class="badge bg-warning text-dark px-2 py-1">
                                <i class="fas fa-fire me-1"></i> هام جداً
                            </span>
                        @else
                            <span class="badge bg-light text-dark border px-2 py-1">عادي</span>
                        @endif
                    </td>
                    <td>
                        @if($ann->is_active)
                            <span class="badge bg-success-subtle text-success border border-success">ظاهر ومفعّل</span>
                        @else
                            <span class="badge bg-secondary-subtle text-muted">متوقف</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $ann->created_at->format('Y-m-d H:i') }}</td>
                    <td class="small text-dark">{{ $ann->publisher->name ?? 'مدير المدرسة' }}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2">
                            {{-- إيقاف / تفعيل --}}
                            <form action="{{ route('manager.announcements.toggle', $ann->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $ann->is_active ? 'btn-outline-warning text-dark' : 'btn-outline-success' }}" title="تغيير حالة الظهور">
                                    <i class="fas {{ $ann->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                            </form>

                            {{-- حذف --}}
                            <form action="{{ route('manager.announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التعميم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف التعميم">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        لا توجد تعاميم أو بلاغات منشورة حالياً. استخدم الزر أعلاه لإصدار تعميم مدرسي.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
</div>

{{-- مودال إضافة تعميم جديد --}}
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('manager.announcements.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fas fa-bullhorn text-danger me-1"></i> صياغة تعميم جديد أو بلاغ طوارئ
                    </h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-end p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الفئة المستهدفة:</label>
                            <select name="target_role" class="form-select text-end" required>
                                <option value="all">كافة منسوبي المدرسة (الجميع)</option>
                                <option value="teacher">كادر المعلمين والمعلمات فقط</option>
                                <option value="parent">أولياء الأمور فقط</option>
                                <option value="student">الطلاب والطالبات فقط</option>
                                <option value="admin">الهيئة الإدارية فقط</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">مستوى الأهمية:</label>
                            <select name="priority" class="form-select text-end" required>
                                <option value="normal">تعميم إداري عادي</option>
                                <option value="high">إعلان هام ومستعجل</option>
                                <option value="emergency">🚨 نداء طوارئ فوري (شريط أحمر بارز في النظام)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان التعميم / البلاغ:</label>
                        <input type="text" name="title" class="form-control text-end" placeholder="مثال: تعليق الدراسة الحضورية ليوم غدٍ نظراً للظروف الجوية" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نص التعميم والتعليمات:</label>
                        <textarea name="content" rows="6" class="form-control text-end" placeholder="اكتب نص التعميم بالتفصيل..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="fas fa-paper-plane me-1"></i> بث ونشر التعميم فوراً
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

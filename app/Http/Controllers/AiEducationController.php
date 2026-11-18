<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiEducationService;
use App\Models\Subject;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\Question;

class AiEducationController extends Controller
{
    protected AiEducationService $aiService;

    public function __construct(AiEducationService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Show AI Quiz Generator Page.
     */
    public function indexGenerator(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $subjects = Subject::where(function($q) use ($schoolId) {
            $q->where('school_id', $schoolId)->orWhereNull('school_id');
        })->get();

        $onlineExams = OnlineExam::where('school_id', $schoolId)
            ->where('teacher_id', auth()->id())
            ->get();

        return view('teacher.ai.generator', compact('subjects', 'onlineExams'));
    }

    /**
     * Generate questions via AI service.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'count' => 'nullable|integer|min:1|max:15',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
        ]);

        $topic = $request->input('topic');
        $subjectName = $request->input('subject_name');
        $count = (int)($request->input('count', 5));
        $difficulty = $request->input('difficulty', 'medium');

        $questions = $this->aiService->generateQuestions($topic, $subjectName, $count, $difficulty);

        return response()->json([
            'success' => true,
            'count' => count($questions),
            'questions' => $questions
        ]);
    }

    /**
     * Save generated questions into an online exam or question bank.
     */
    public function saveToExam(Request $request)
    {
        $request->validate([
            'online_exam_id' => 'required|exists:online_exams,id',
            'questions' => 'required|array|min:1',
        ]);

        $exam = OnlineExam::findOrFail($request->online_exam_id);
        
        // Authorization check
        if ($exam->school_id != auth()->user()->school_id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا الاختبار.'], 403);
        }

        $savedCount = 0;
        foreach ($request->questions as $q) {
            if (!empty($q['question_text']) && !empty($q['correct_answer'])) {
                OnlineExamQuestion::create([
                    'online_exam_id' => $exam->id,
                    'question_text' => $q['question_text'],
                    'question_type' => 'multiple_choice',
                    'options' => is_array($q['options']) ? json_encode($q['options'], JSON_UNESCAPED_UNICODE) : $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'score' => (int)($q['score'] ?? 1),
                ]);
                $savedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم حفظ {$savedCount} أسئلة بنجاح في الاختبار المختار! ✅",
            'redirect' => route('teacher.online_exams.show', $exam->id)
        ]);
    }

    /**
     * AI Early Warning Dashboard for At-Risk Students.
     */
    public function earlyWarningDashboard(Request $request)
    {
        $schoolId = auth()->user()->school_id ?? 1;
        $atRiskStudents = $this->aiService->analyzeAcademicRisk($schoolId);

        $criticalCount = count(array_filter($atRiskStudents, fn($s) => $s['risk_score'] >= 60));
        $moderateCount = count(array_filter($atRiskStudents, fn($s) => $s['risk_score'] < 60 && $s['risk_score'] >= 30));

        return view('admin.ai.early_warning', compact('atRiskStudents', 'criticalCount', 'moderateCount'));
    }
}

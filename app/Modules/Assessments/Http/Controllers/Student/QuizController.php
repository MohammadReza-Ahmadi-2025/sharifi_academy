<?php

namespace App\Modules\Assessments\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Assessments\Http\Requests\Student\SubmitQuizAttemptRequest;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizAttempt;
use App\Modules\Assessments\Services\CertificateIssueService;
use App\Modules\Assessments\Services\QuizAttemptService;
use App\Modules\Assessments\Support\QuizPresenter;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $this->authorize('viewAny', Quiz::class);

        $courseIds = Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->with('courseClass')
            ->get()
            ->pluck('courseClass.course_id')
            ->filter()
            ->unique()
            ->values();

        $quizzes = Quiz::query()
            ->published()
            ->whereIn('course_id', $courseIds)
            ->with('course')
            ->orderByDesc('id')
            ->get()
            ->map(function (Quiz $quiz) use ($user): array {
                $latest = QuizAttempt::query()
                    ->where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->whereNotNull('submitted_at')
                    ->orderByDesc('id')
                    ->first();

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'course_title' => $quiz->course?->title,
                    'pass_score' => $quiz->pass_score,
                    'latest_score' => $latest?->score,
                    'latest_passed' => $latest?->passed,
                    'show_url' => route('student.quizzes.show', $quiz),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Student/Quizzes/Index', [
            'quizzes' => $quizzes,
        ]);
    }

    public function show(Request $request, Quiz $quiz): Response
    {
        $this->authorize('view', $quiz);

        $latest = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $request->user()->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('id')
            ->first();

        return Inertia::render('Student/Quizzes/Show', [
            'quiz' => QuizPresenter::forStudent($quiz, $latest),
        ]);
    }

    public function store(
        SubmitQuizAttemptRequest $request,
        Quiz $quiz,
        QuizAttemptService $attempts,
        CertificateIssueService $certificates,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $answers = [];
        foreach ($request->validated('answers') as $questionId => $optionId) {
            $answers[(int) $questionId] = $optionId !== null ? (int) $optionId : null;
        }

        $attempt = $attempts->submit($quiz, $user, $answers);

        if ($attempt->passed && $quiz->course) {
            try {
                $certificates->issue($quiz->course, $user, null, false);
            } catch (\Throwable) {
                // صدور خودکار فقط در صورت واجد شرایط بودن؛ خطای اعتبارسنجی نادیده گرفته می‌شود.
            }
        }

        return redirect()
            ->route('student.quizzes.show', $quiz)
            ->with('success', $attempt->passed
                ? "قبول شدید با نمره {$attempt->score}"
                : "نمره شما {$attempt->score} — حد قبولی {$quiz->pass_score}");
    }
}

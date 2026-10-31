<?php

namespace App\Modules\Assessments\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Assessments\Http\Requests\Instructor\StoreQuizRequest;
use App\Modules\Assessments\Http\Requests\Instructor\UpdateQuizRequest;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Services\QuizSyncService;
use App\Modules\Assessments\Support\QuizPresenter;
use App\Modules\Courses\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseQuizController extends Controller
{
    public function index(Request $request, Course $course): Response
    {
        $this->authorize('view', $course);
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::query()
            ->where('course_id', $course->id)
            ->withCount(['questions', 'attempts'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Quiz $quiz) => QuizPresenter::forList($quiz))
            ->values()
            ->all();

        return Inertia::render('Instructor/Courses/Quizzes/Index', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'show_url' => route('instructor.courses.show', $course),
                'create_url' => route('instructor.courses.quizzes.create', $course),
                'certificate_enabled' => (bool) $course->certificate_enabled,
                'certificate_title' => $course->certificate_title,
                'certificate_min_attendance_percent' => $course->certificate_min_attendance_percent,
                'certificate_min_quiz_score' => $course->certificate_min_quiz_score,
            ],
            'quizzes' => $quizzes,
            'canCreate' => $request->user()?->can('create', Quiz::class) ?? false,
        ]);
    }

    public function create(Course $course): Response
    {
        $this->authorize('update', $course);
        $this->authorize('create', Quiz::class);

        return Inertia::render('Instructor/Courses/Quizzes/Form', [
            'course' => ['id' => $course->id, 'title' => $course->title],
            'quiz' => null,
        ]);
    }

    public function store(StoreQuizRequest $request, Course $course, QuizSyncService $sync): RedirectResponse
    {
        $quiz = $sync->create($course->id, $request->validated());

        return redirect()
            ->route('instructor.courses.quizzes.edit', [$course, $quiz])
            ->with('success', 'آزمون ذخیره شد.');
    }

    public function edit(Course $course, Quiz $quiz): Response
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        $this->authorize('update', $quiz);

        return Inertia::render('Instructor/Courses/Quizzes/Form', [
            'course' => ['id' => $course->id, 'title' => $course->title],
            'quiz' => QuizPresenter::forForm($quiz),
        ]);
    }

    public function update(
        UpdateQuizRequest $request,
        Course $course,
        Quiz $quiz,
        QuizSyncService $sync,
    ): RedirectResponse {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);

        $sync->update($quiz, $request->validated());

        return redirect()
            ->route('instructor.courses.quizzes.edit', [$course, $quiz])
            ->with('success', 'آزمون به‌روزرسانی شد.');
    }

    public function destroy(Course $course, Quiz $quiz): RedirectResponse
    {
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return redirect()
            ->route('instructor.courses.quizzes.index', $course)
            ->with('success', 'آزمون حذف شد.');
    }
}

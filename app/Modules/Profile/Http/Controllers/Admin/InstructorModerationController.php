<?php

namespace App\Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Http\Requests\Admin\RejectInstructorResumeRequest;
use App\Modules\Profile\Support\InstructorPublicResumePresenter;
use App\Modules\Profile\Support\ResumeModeration;
use App\Support\Notifications\InstructorModerationNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstructorModerationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('instructor-moderation-index'), 403);

        $pendingCourses = Course::query()
            ->where('status', CourseStatus::PendingReview)
            ->whereNotNull('created_by')
            ->whereHas('creator.roles', fn ($q) => $q->where('name', 'instructor'))
            ->with('creator')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get()
            ->map(function (Course $course) {
                $row = CoursePresenter::forList($course);
                $creator = $course->creator;
                $row['creator_name'] = $creator
                    ? trim(($creator->name ?? '').' '.($creator->family ?? ''))
                    : null;

                return $row;
            })
            ->values()
            ->all();

        $pendingResumes = User::query()
            ->where('status', 1)
            ->role(['instructor', 'student'])
            ->whereHas('profile', fn ($q) => $q->where('resume_moderation_status', ResumeModerationStatus::Pending->value))
            ->with(['profile', 'roles'])
            ->withCount('resumes')
            ->limit(100)
            ->get()
            ->sortByDesc(fn (User $user) => $user->profile?->resume_submitted_at?->timestamp ?? 0)
            ->values()
            ->map(function (User $user) {
                $profile = $user->profile;
                $roleLabel = $user->hasRole('instructor') ? 'استاد' : ($user->hasRole('student') ? 'دانشجو' : '—');

                return [
                    'id' => $user->id,
                    'full_name' => trim(($user->name ?? '').' '.($user->family ?? '')) ?: '—',
                    'mobile' => $user->mobile,
                    'role_label' => $roleLabel,
                    'resume_items_count' => (int) ($user->resumes_count ?? 0),
                    'submitted_at' => $profile?->resume_submitted_at?->toIso8601String(),
                    'review_url' => route('admin.instructor-moderation.resumes.show', $user),
                ];
            })
            ->all();

        return Inertia::render('Admin/InstructorModeration/Index', [
            'pendingCourses' => $pendingCourses,
            'pendingResumes' => $pendingResumes,
            'counts' => [
                'courses' => count($pendingCourses),
                'resumes' => count($pendingResumes),
            ],
            'abilities' => [
                'moderate' => $request->user()?->can('instructor-moderation-update') ?? false,
            ],
        ]);
    }

    public function showResume(Request $request, User $user): Response
    {
        abort_unless($request->user()?->can('instructor-moderation-index'), 403);
        abort_unless($user->hasAnyRole(['instructor', 'student']), 404);
        abort_unless(ResumeModeration::status($user) === ResumeModerationStatus::Pending, 404);

        $user->load([
            'profile',
            'skills',
            'resumes' => fn ($query) => $query->with('media')->orderBy('display_order')->orderByDesc('start_date'),
        ]);

        return Inertia::render('Admin/InstructorModeration/ResumeShow', [
            'profile' => InstructorPublicResumePresenter::forUser($user),
            'moderation' => [
                'status' => ResumeModeration::status($user)->value,
                'status_label' => ResumeModeration::status($user)->label(),
                'note' => $user->profile?->resume_moderation_note,
                'submitted_at' => $user->profile?->resume_submitted_at?->toIso8601String(),
                'role_label' => $user->hasRole('instructor') ? 'استاد' : 'دانشجو',
            ],
            'abilities' => [
                'moderate' => $request->user()?->can('instructor-moderation-update') ?? false,
            ],
        ]);
    }

    public function approveCourse(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()?->can('instructor-moderation-update'), 403);
        abort_unless($course->status === CourseStatus::PendingReview, 422);

        $course->update([
            'status' => CourseStatus::Published,
            'published_at' => $course->published_at ?? now(),
        ]);

        InstructorModerationNotifier::courseModerationResult($course->fresh(), true);

        return back()->with('success', 'دوره تایید و منتشر شد.');
    }

    public function rejectCourse(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()?->can('instructor-moderation-update'), 403);
        abort_unless($course->status === CourseStatus::PendingReview, 422);

        $course->update([
            'status' => CourseStatus::Draft,
        ]);

        InstructorModerationNotifier::courseModerationResult($course->fresh(), false);

        return back()->with('success', 'دوره به پیش‌نویس برگردانده شد.');
    }

    public function approveResume(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('instructor-moderation-update'), 403);
        abort_unless($user->hasAnyRole(['instructor', 'student']), 404);
        abort_unless(ResumeModeration::status($user) === ResumeModerationStatus::Pending, 404);

        ResumeModeration::markApproved($user, $request->user());

        InstructorModerationNotifier::resumeModerationResult($user, true);

        $label = $user->hasRole('instructor') ? 'استاد' : 'دانشجو';

        return back()->with('success', "رزومه {$label} تایید شد.");
    }

    public function rejectResume(RejectInstructorResumeRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(['instructor', 'student']), 404);
        abort_unless(ResumeModeration::status($user) === ResumeModerationStatus::Pending, 404);

        $note = $request->validated('note');

        ResumeModeration::markRejected($user, $note, $request->user());

        InstructorModerationNotifier::resumeModerationResult($user, false, $note);

        return back()->with('success', 'رزومه رد شد؛ کاربر می‌تواند اصلاح و دوباره ارسال کند.');
    }
}

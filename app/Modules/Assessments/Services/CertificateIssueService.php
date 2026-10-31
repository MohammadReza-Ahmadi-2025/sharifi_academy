<?php

namespace App\Modules\Assessments\Services;

use App\Models\User;
use App\Modules\Assessments\Models\Certificate;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizAttempt;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Models\SessionAttendance;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateIssueService
{
    /**
     * @return array{eligible: bool, reasons: list<string>, attendance_rate: int|null, best_quiz_score: float|null}
     */
    public function eligibility(Course $course, User $user): array
    {
        $reasons = [];

        if (! $course->certificate_enabled) {
            $reasons[] = 'صدور گواهینامه برای این دوره فعال نیست.';
        }

        $classIds = $course->courseClasses()->pluck('id');
        $confirmed = Enrollment::query()
            ->whereIn('course_class_id', $classIds)
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->exists();

        if (! $confirmed) {
            $reasons[] = 'ثبت‌نام قطعی در کلاس این دوره لازم است.';
        }

        $attendanceRate = $this->attendanceRate($user->id, $classIds->all());
        $minAttendance = $course->certificate_min_attendance_percent;
        if ($minAttendance !== null && ($attendanceRate === null || $attendanceRate < $minAttendance)) {
            $reasons[] = "حداقل حضور {$minAttendance}٪ لازم است.";
        }

        $bestQuiz = $this->bestQuizScore($course->id, $user->id);
        $minQuiz = $course->certificate_min_quiz_score;
        if ($minQuiz !== null && ($bestQuiz === null || $bestQuiz < $minQuiz)) {
            $reasons[] = "حداقل نمره آزمون {$minQuiz} لازم است.";
        }

        return [
            'eligible' => $reasons === [],
            'reasons' => $reasons,
            'attendance_rate' => $attendanceRate,
            'best_quiz_score' => $bestQuiz,
        ];
    }

    public function issue(Course $course, User $user, ?User $issuer = null, bool $force = false): Certificate
    {
        $existing = Certificate::query()
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        if (! $force) {
            $eligibility = $this->eligibility($course, $user);
            if (! $eligibility['eligible']) {
                throw ValidationException::withMessages([
                    'certificate' => $eligibility['reasons'][0] ?? 'شرایط صدور گواهینامه برقرار نیست.',
                ]);
            }
        }

        $eligibility = $this->eligibility($course, $user);

        return Certificate::query()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'code' => $this->uniqueCode(),
            'title' => $course->certificate_title ?: ('گواهینامه '.$course->title),
            'issued_at' => now(),
            'issued_by' => $issuer?->id,
            'meta' => [
                'attendance_rate' => $eligibility['attendance_rate'],
                'best_quiz_score' => $eligibility['best_quiz_score'],
                'forced' => $force,
            ],
        ]);
    }

    /**
     * @param  list<int>  $classIds
     */
    private function attendanceRate(int $userId, array $classIds): ?int
    {
        if ($classIds === []) {
            return null;
        }

        $rows = SessionAttendance::query()
            ->where('user_id', $userId)
            ->whereHas('classSession', fn ($query) => $query->whereIn('course_class_id', $classIds))
            ->get(['status']);

        if ($rows->isEmpty()) {
            return null;
        }

        $present = $rows->filter(fn (SessionAttendance $row) => $row->status === SessionAttendanceStatus::Present)->count();
        $absent = $rows->filter(fn (SessionAttendance $row) => $row->status === SessionAttendanceStatus::Absent)->count();
        $scored = $present + $absent;

        return $scored > 0 ? (int) round(($present / $scored) * 100) : null;
    }

    private function bestQuizScore(int $courseId, int $userId): ?float
    {
        $quizIds = Quiz::query()->where('course_id', $courseId)->pluck('id');
        if ($quizIds->isEmpty()) {
            return null;
        }

        $best = QuizAttempt::query()
            ->whereIn('quiz_id', $quizIds)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->max('score');

        return $best !== null ? (float) $best : null;
    }

    private function uniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (Certificate::query()->where('code', $code)->exists());

        return $code;
    }
}

<?php

namespace Tests\Feature\Assessments;

use App\Models\User;
use App\Modules\Assessments\Models\Certificate;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizOption;
use App\Modules\Assessments\Models\QuizQuestion;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAndCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_creates_quiz_and_student_submits_then_claims_certificate(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره آزمون',
            'slug' => 'quiz-cert-course',
            'status' => CourseStatus::Published,
            'created_by' => $instructor->id,
            'certificate_enabled' => true,
            'certificate_title' => 'گواهی دوره آزمون',
            'certificate_min_quiz_score' => 70,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'کلاس آزمون',
            'code' => 'QZ-1',
            'status' => CourseClassStatus::Ongoing,
            'price' => 0,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);

        $this->actingAs($instructor)
            ->post("/instructor/courses/{$course->id}/quizzes", [
                'title' => 'کوییز ۱',
                'description' => 'تست',
                'pass_score' => 70,
                'is_published' => true,
                'questions' => [
                    [
                        'prompt' => '۲+۲؟',
                        'points' => 1,
                        'options' => [
                            ['label' => '۳', 'is_correct' => false],
                            ['label' => '۴', 'is_correct' => true],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $quiz = Quiz::query()->where('course_id', $course->id)->firstOrFail();
        $question = QuizQuestion::query()->where('quiz_id', $quiz->id)->firstOrFail();
        $correct = QuizOption::query()->where('quiz_question_id', $question->id)->where('is_correct', true)->firstOrFail();

        $this->actingAs($student)
            ->get('/student/quizzes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Student/Quizzes/Index')->has('quizzes', 1));

        $this->actingAs($student)
            ->post("/student/quizzes/{$quiz->id}/attempts", [
                'answers' => [$question->id => $correct->id],
            ])
            ->assertRedirect();

        $this->actingAs($student)
            ->post("/student/courses/{$course->id}/certificates/claim")
            ->assertRedirect();

        $this->assertDatabaseHas('certificates', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        $certificate = Certificate::query()->where('course_id', $course->id)->where('user_id', $student->id)->firstOrFail();

        $this->get('/certificates/verify/'.$certificate->code)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/CertificateVerify')
                ->where('certificate.valid', true));
    }
}

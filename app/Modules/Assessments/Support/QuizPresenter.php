<?php

namespace App\Modules\Assessments\Support;

use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizAttempt;

final class QuizPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'is_published' => $quiz->is_published,
            'pass_score' => $quiz->pass_score,
            'questions_count' => $quiz->questions_count ?? $quiz->questions()->count(),
            'attempts_count' => $quiz->attempts_count ?? $quiz->attempts()->count(),
            'edit_url' => route('instructor.courses.quizzes.edit', [$quiz->course_id, $quiz]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Quiz $quiz): array
    {
        $quiz->loadMissing(['questions.options']);

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'pass_score' => $quiz->pass_score,
            'is_published' => $quiz->is_published,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'max_attempts' => $quiz->max_attempts,
            'questions' => $quiz->questions->map(fn ($question) => [
                'prompt' => $question->prompt,
                'points' => $question->points,
                'options' => $question->options->map(fn ($option) => [
                    'label' => $option->label,
                    'is_correct' => $option->is_correct,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forStudent(Quiz $quiz, ?QuizAttempt $latestAttempt = null): array
    {
        $quiz->loadMissing(['questions.options', 'course']);

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'pass_score' => $quiz->pass_score,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'max_attempts' => $quiz->max_attempts,
            'course_title' => $quiz->course?->title,
            'questions' => $quiz->questions->map(fn ($question) => [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'points' => $question->points,
                'options' => $question->options->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                ])->values()->all(),
            ])->values()->all(),
            'latest_attempt' => $latestAttempt === null ? null : [
                'score' => $latestAttempt->score,
                'passed' => $latestAttempt->passed,
                'submitted_at' => $latestAttempt->submitted_at?->toIso8601String(),
            ],
        ];
    }
}

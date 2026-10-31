<?php

namespace App\Modules\Assessments\Services;

use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizOption;
use App\Modules\Assessments\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;

class QuizSyncService
{
    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     pass_score?: int,
     *     is_published?: bool,
     *     time_limit_minutes?: int|null,
     *     max_attempts?: int|null,
     *     questions?: list<array{prompt: string, points?: int, options: list<array{label: string, is_correct?: bool}>}>
     * }  $data
     */
    public function create(int $courseId, array $data): Quiz
    {
        return DB::transaction(function () use ($courseId, $data): Quiz {
            $quiz = Quiz::query()->create([
                'course_id' => $courseId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'pass_score' => (int) ($data['pass_score'] ?? 70),
                'is_published' => (bool) ($data['is_published'] ?? false),
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'max_attempts' => $data['max_attempts'] ?? null,
            ]);

            $this->syncQuestions($quiz, $data['questions'] ?? []);

            return $quiz->fresh(['questions.options']) ?? $quiz;
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     pass_score?: int,
     *     is_published?: bool,
     *     time_limit_minutes?: int|null,
     *     max_attempts?: int|null,
     *     questions?: list<array{prompt: string, points?: int, options: list<array{label: string, is_correct?: bool}>}>
     * }  $data
     */
    public function update(Quiz $quiz, array $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data): Quiz {
            $quiz->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'pass_score' => (int) ($data['pass_score'] ?? 70),
                'is_published' => (bool) ($data['is_published'] ?? false),
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'max_attempts' => $data['max_attempts'] ?? null,
            ]);

            $quiz->questions()->each(function (QuizQuestion $question): void {
                $question->options()->delete();
                $question->delete();
            });

            $this->syncQuestions($quiz, $data['questions'] ?? []);

            return $quiz->fresh(['questions.options']) ?? $quiz;
        });
    }

    /**
     * @param  list<array{prompt: string, points?: int, options: list<array{label: string, is_correct?: bool}>}>  $questions
     */
    private function syncQuestions(Quiz $quiz, array $questions): void
    {
        foreach (array_values($questions) as $qIndex => $questionData) {
            $question = QuizQuestion::query()->create([
                'quiz_id' => $quiz->id,
                'prompt' => $questionData['prompt'],
                'sort' => $qIndex,
                'points' => (int) ($questionData['points'] ?? 1),
            ]);

            foreach (array_values($questionData['options'] ?? []) as $oIndex => $optionData) {
                QuizOption::query()->create([
                    'quiz_question_id' => $question->id,
                    'label' => $optionData['label'],
                    'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                    'sort' => $oIndex,
                ]);
            }
        }
    }
}

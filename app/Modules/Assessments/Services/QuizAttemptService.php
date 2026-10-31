<?php

namespace App\Modules\Assessments\Services;

use App\Models\User;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Assessments\Models\QuizAttempt;
use App\Modules\Assessments\Models\QuizAttemptAnswer;
use App\Modules\Assessments\Models\QuizOption;
use App\Modules\Assessments\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptService
{
    /**
     * @param  array<int, int|null>  $answers  question_id => option_id
     */
    public function submit(Quiz $quiz, User $user, array $answers): QuizAttempt
    {
        $quiz->load(['questions.options']);

        if (! $quiz->is_published) {
            throw ValidationException::withMessages(['quiz' => 'این آزمون هنوز منتشر نشده است.']);
        }

        $attemptsCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->count();

        if ($quiz->max_attempts !== null && $attemptsCount >= $quiz->max_attempts) {
            throw ValidationException::withMessages(['quiz' => 'سقف تعداد تلاش برای این آزمون پر شده است.']);
        }

        $totalPoints = max(1, (int) $quiz->questions->sum('points'));
        $earned = 0;

        foreach ($quiz->questions as $question) {
            $selectedOptionId = $answers[$question->id] ?? null;
            if ($selectedOptionId === null) {
                continue;
            }

            $option = $question->options->firstWhere('id', (int) $selectedOptionId);
            if ($option instanceof QuizOption && $option->is_correct) {
                $earned += (int) $question->points;
            }
        }

        $score = round(($earned / $totalPoints) * 100, 2);
        $passed = $score >= (int) $quiz->pass_score;

        return DB::transaction(function () use ($quiz, $user, $answers, $score, $passed): QuizAttempt {
            $attempt = QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => $score,
                'passed' => $passed,
                'started_at' => now(),
                'submitted_at' => now(),
            ]);

            foreach ($quiz->questions as $question) {
                /** @var QuizQuestion $question */
                $optionId = $answers[$question->id] ?? null;
                QuizAttemptAnswer::query()->create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id' => $optionId !== null ? (int) $optionId : null,
                ]);
            }

            return $attempt;
        });
    }
}

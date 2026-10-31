<?php

namespace App\Modules\Assessments\Http\Requests\Instructor;

use App\Modules\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course $course */
        $course = $this->route('course');

        return $this->user()?->can('create', [\App\Modules\Assessments\Models\Quiz::class])
            && $this->user()?->can('update', $course);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pass_score' => ['required', 'integer', 'min:1', 'max:100'],
            'is_published' => ['boolean'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:50'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.prompt' => ['required', 'string', 'max:2000'],
            'questions.*.points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.label' => ['required', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('questions', []) as $index => $question) {
                $correctCount = collect($question['options'] ?? [])
                    ->filter(fn ($option) => (bool) ($option['is_correct'] ?? false))
                    ->count();

                if ($correctCount !== 1) {
                    $validator->errors()->add(
                        "questions.{$index}.options",
                        'هر سؤال باید دقیقاً یک گزینه صحیح داشته باشد.',
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان آزمون',
            'pass_score' => 'نمره قبولی',
            'questions' => 'سؤالات',
        ];
    }
}

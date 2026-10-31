<?php

namespace App\Modules\Assessments\Http\Requests\Student;

use App\Modules\Assessments\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');

        return $this->user()?->can('attempt', $quiz) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ];
    }
}

<?php

namespace App\Modules\Assessments\Http\Requests\Instructor;

use App\Modules\Assessments\Models\Quiz;

class UpdateQuizRequest extends StoreQuizRequest
{
    public function authorize(): bool
    {
        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');

        return $this->user()?->can('update', $quiz) ?? false;
    }
}

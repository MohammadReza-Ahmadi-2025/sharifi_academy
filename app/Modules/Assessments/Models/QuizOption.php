<?php

namespace App\Modules\Assessments\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quiz_question_id',
    'label',
    'is_correct',
    'sort',
])]
class QuizOption extends Model
{
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}

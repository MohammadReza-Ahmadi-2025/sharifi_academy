<?php

namespace App\Modules\Profile\Http\Requests\Instructor;

use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncInstructorSkillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('syncSkills', Resume::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'skills' => ['present', 'array', 'max:50'],
            'skills.*.skill_id' => ['required', 'integer', Rule::exists('skills', 'id')],
            'skills.*.level' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * @return array<int, array{skill_id: int, level: int|null}>
     */
    public function skillRows(): array
    {
        $rows = [];
        $seen = [];

        foreach ($this->input('skills', []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $skillId = (int) ($row['skill_id'] ?? 0);
            if ($skillId <= 0 || isset($seen[$skillId])) {
                continue;
            }

            $seen[$skillId] = true;
            $level = isset($row['level']) && is_numeric($row['level']) ? (int) $row['level'] : null;

            $rows[] = [
                'skill_id' => $skillId,
                'level' => $level,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'skills.*.skill_id.exists' => 'یکی از مهارت‌های انتخاب‌شده معتبر نیست.',
            'skills.*.level.min' => 'سطح مهارت باید بین ۱ تا ۵ باشد.',
            'skills.*.level.max' => 'سطح مهارت باید بین ۱ تا ۵ باشد.',
        ];
    }
}

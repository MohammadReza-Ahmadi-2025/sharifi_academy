<?php

namespace App\Modules\Profile\Http\Requests\Admin;

use App\Modules\Profile\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkillRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('category_id') === '' || $this->input('category_id') === null) {
            $this->merge(['category_id' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('skills-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $skill = $this->route('skill');
        $id = $skill instanceof Skill ? $skill->id : $skill;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('skills', 'name')->ignore($id)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'course')],
        ];
    }
}

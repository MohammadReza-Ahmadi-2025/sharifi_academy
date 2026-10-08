<?php

namespace App\Modules\Stories\Http\Requests\Admin;

use App\Modules\Stories\Http\Requests\Admin\Concerns\ValidatesAdminStoryFields;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminStoryRequest extends FormRequest
{
    use ValidatesAdminStoryFields;

    public function authorize(): bool
    {
        return $this->user()?->can('stories-create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStoryFields();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->storyFieldRules(requireMedia: true);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->storyFieldMessages();
    }
}

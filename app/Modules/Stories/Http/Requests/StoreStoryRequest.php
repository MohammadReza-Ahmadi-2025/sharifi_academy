<?php

namespace App\Modules\Stories\Http\Requests;

use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['required', Rule::enum(StoryType::class)],
            'content' => [
                Rule::requiredIf($type === StoryType::Text->value),
                'nullable',
                'string',
                'max:5000',
            ],
            'background_color' => ['nullable', 'string', 'max:20'],
            'visibility' => ['nullable', Rule::enum(StoryVisibility::class)],
            'media' => [
                Rule::requiredIf(in_array($type, [StoryType::Image->value, StoryType::Video->value], true)),
                'nullable',
                'file',
                'max:51200',
            ],
        ];
    }
}

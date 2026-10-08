<?php

namespace App\Modules\Home\Http\Requests\Admin;

class UpdateHomeSectionRequest extends StoreHomeSectionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('home-sections-update') ?? false;
    }
}

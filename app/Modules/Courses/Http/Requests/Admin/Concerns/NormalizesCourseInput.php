<?php

namespace App\Modules\Courses\Http\Requests\Admin\Concerns;

trait NormalizesCourseInput
{
    protected function prepareForValidation(): void
    {
        $duration = $this->input('duration_minutes');

        $this->merge([
            'slug' => $this->filled('slug') ? $this->input('slug') : null,
            'code' => $this->filled('code') ? $this->input('code') : null,
            'level' => $this->filled('level') ? $this->input('level') : null,
            'published_at' => $this->filled('published_at') ? $this->input('published_at') : null,
            'duration_minutes' => $duration === '' || $duration === null ? null : $duration,
        ]);
    }
}

<?php

namespace App\Modules\Menu\Http\Requests\Admin;

class UpdateMenuRequest extends StoreMenuRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus-update') ?? false;
    }
}

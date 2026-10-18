<?php

namespace App\Modules\Courses\Http\Requests\Admin\Concerns;

use App\Models\User;
use App\Modules\Profile\Support\UserProfileCompleteness;
use Illuminate\Validation\Validator;

trait ValidatesEnrollableUser
{
    public function withEnrollableUserValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $userId = $this->input('user_id');
            if (! is_numeric($userId)) {
                return;
            }

            $user = User::query()->find((int) $userId);
            if ($user === null) {
                return;
            }

            if (! UserProfileCompleteness::isComplete($user)) {
                $validator->errors()->add('user_id', UserProfileCompleteness::enrollmentBlockMessage());
            }
        });
    }
}

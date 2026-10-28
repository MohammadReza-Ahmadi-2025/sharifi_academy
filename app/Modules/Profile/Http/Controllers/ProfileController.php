<?php

namespace App\Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Profile\Http\Requests\UpdateProfileRequest;
use App\Modules\Profile\Support\LocationSelectOptions;
use App\Modules\Profile\Support\ProfilePayload;
use App\Modules\Profile\Support\ProfileUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Admin/Profile/Edit', [
            'profile' => ProfilePayload::forUser($user),
            'locations' => LocationSelectOptions::forForms(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        ProfileUpdater::updateUserAndProfile($user, $request->validated(), $request);

        return back()->with('success', 'پروفایل به‌روزرسانی شد.');
    }
}

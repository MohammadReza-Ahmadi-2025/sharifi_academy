<?php

namespace App\Modules\Profile\Http\Controllers\Concerns;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Http\Requests\Instructor\StoreResumeRequest;
use App\Modules\Profile\Http\Requests\Instructor\SyncInstructorSkillsRequest;
use App\Modules\Profile\Http\Requests\Instructor\UpdateResumeRequest;
use App\Modules\Profile\Http\Requests\SubmitResumeForReviewRequest;
use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Models\Skill;
use App\Modules\Profile\Support\InstructorPublicResumePresenter;
use App\Modules\Profile\Support\LocationSelectOptions;
use App\Modules\Profile\Support\ProfilePayload;
use App\Modules\Profile\Support\ResumeAttachmentMedia;
use App\Modules\Profile\Support\ResumeEntryPresenter;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Profile\Support\ResumeTypeOptions;
use App\Support\Notifications\InstructorModerationNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

trait ManagesOwnResume
{
    abstract protected function resumeInertiaPage(): string;

    abstract protected function resumeIndexRoute(): string;

    /**
     * Panel-scoped permission so student/instructor routes cannot be mixed by permission alone.
     */
    abstract protected function requiredResumePermission(): string;

    /**
     * @return array{base_path: string, panel: string, public_visibility: bool}
     */
    abstract protected function resumePanelMeta(): array;

    public function index(Request $request): Response
    {
        $this->ensurePanelResumeAccess($request);
        $this->authorize('viewAny', Resume::class);

        $user = $request->user();
        abort_if($user === null, 403);

        $user->load([
            'profile',
            'resumes' => fn ($query) => $query
                ->with('media')
                ->orderBy('display_order')
                ->orderByDesc('start_date'),
            'skills',
        ]);

        $items = $user->resumes
            ->map(fn (Resume $resume) => ResumeEntryPresenter::forInstructor($resume))
            ->values()
            ->all();

        $profile = InstructorPublicResumePresenter::forUser($user);
        $moderationStatus = ResumeModeration::status($user);
        $meta = $this->resumePanelMeta();

        return Inertia::render($this->resumeInertiaPage(), [
            'items' => $items,
            'sections' => $profile['sections'],
            'profilePreview' => [
                'id' => $profile['id'],
                'uuid' => $profile['uuid'],
                'fullName' => $profile['fullName'],
                'headline' => $profile['headline'],
                'bio' => $profile['bio'],
                'avatar' => $profile['avatar'],
                'profile_url' => $profile['profile_url'],
                'skills' => $profile['skills'],
            ],
            'typeOptions' => ResumeTypeOptions::forSelect(),
            'attachmentLimits' => [
                'max_files' => ResumeAttachmentMedia::MAX_FILES,
                'max_kilobytes' => ResumeAttachmentMedia::MAX_KILOBYTES,
            ],
            'moderation' => [
                'status' => $moderationStatus->value,
                'status_label' => $moderationStatus->label(),
                'note' => $user->profile?->resume_moderation_note,
                'can_submit' => $user->resumes->isNotEmpty()
                    && $moderationStatus !== ResumeModerationStatus::Pending,
            ],
            'skillOptions' => Skill::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Skill $skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ])
                ->values()
                ->all(),
            'selectedSkills' => $user->skills
                ->map(fn ($skill) => [
                    'skill_id' => $skill->id,
                    'level' => $skill->pivot?->level,
                ])
                ->values()
                ->all(),
            'panel' => $meta,
            'accountProfile' => ProfilePayload::forUser($user),
            'locations' => LocationSelectOptions::forForms(),
        ]);
    }

    public function syncSkills(SyncInstructorSkillsRequest $request): RedirectResponse
    {
        $this->ensurePanelResumeAccess($request);

        $user = $request->user();
        abort_if($user === null, 403);

        $sync = [];
        foreach ($request->skillRows() as $row) {
            $sync[$row['skill_id']] = ['level' => $row['level']];
        }

        $user->skills()->sync($sync);
        $this->markResumeNeedsReview($user);

        return redirect()
            ->route($this->resumeIndexRoute())
            ->with('success', 'مهارت‌ها ذخیره شد.');
    }

    public function submitForReview(SubmitResumeForReviewRequest $request): RedirectResponse
    {
        $this->ensurePanelResumeAccess($request);
        $this->authorize('viewAny', Resume::class);

        $user = $request->user();
        abort_if($user === null, 403);

        abort_if($user->resumes()->count() < 1, 422, 'حداقل یک مورد رزومه ثبت کنید.');

        ResumeModeration::markPending($user);

        InstructorModerationNotifier::resumeSubmittedForReview($user);

        return redirect()
            ->route($this->resumeIndexRoute())
            ->with('success', 'رزومه برای بررسی ادمین ارسال شد.');
    }

    public function store(StoreResumeRequest $request): RedirectResponse
    {
        $this->ensurePanelResumeAccess($request);

        $user = $request->user();
        abort_if($user === null, 403);

        $data = $request->validated();
        unset($data['attachments'], $data['remove_attachment_ids']);

        $resume = $user->resumes()->create([
            ...$data,
            'is_current' => $data['is_current'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        ResumeAttachmentMedia::syncFromRequest($resume, $request);

        return redirect()
            ->route($this->resumeIndexRoute())
            ->with('success', 'مورد رزومه ثبت شد.');
    }

    public function update(UpdateResumeRequest $request, Resume $resume): RedirectResponse
    {
        $this->ensurePanelResumeAccess($request);
        $this->authorize('update', $resume);

        $data = $request->validated();
        unset($data['attachments'], $data['remove_attachment_ids']);

        $resume->update([
            ...$data,
            'is_current' => $data['is_current'] ?? false,
            'display_order' => $data['display_order'] ?? $resume->display_order,
        ]);

        ResumeAttachmentMedia::syncFromRequest($resume, $request);
        $this->markResumeNeedsReview($request->user());

        return redirect()
            ->route($this->resumeIndexRoute())
            ->with('success', 'رزومه به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Resume $resume): RedirectResponse
    {
        $this->ensurePanelResumeAccess($request);
        $this->authorize('delete', $resume);

        $user = $request->user();
        $resume->clearMediaCollection(ResumeAttachmentMedia::COLLECTION);
        $resume->delete();

        if ($user !== null) {
            $this->markResumeNeedsReview($user);
        }

        return redirect()
            ->route($this->resumeIndexRoute())
            ->with('success', 'مورد رزومه حذف شد.');
    }

    private function ensurePanelResumeAccess(Request $request): void
    {
        abort_unless($request->user()?->can($this->requiredResumePermission()), 403);
    }

    private function markResumeNeedsReview(?User $user): void
    {
        if ($user === null) {
            return;
        }

        $status = ResumeModeration::status($user);
        if ($status === ResumeModerationStatus::Approved) {
            ResumeModeration::markPending($user);
        }
    }
}

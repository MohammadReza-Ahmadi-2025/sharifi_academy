<?php

namespace App\Modules\Comments\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Comments\Enums\CommenterType;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Http\Requests\Site\StoreCourseCommentRequest;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Illuminate\Http\RedirectResponse;

class CourseCommentController extends Controller
{
    public function store(StoreCourseCommentRequest $request, Course $course): RedirectResponse
    {
        abort_unless($course->status === CourseStatus::Published && ! $course->trashed(), 404);

        $data = $request->validated();

        $course->comments()->create([
            'user_id' => $request->user()->id,
            'commenter_type' => CommenterType::User,
            'body' => $data['body'],
            'rating' => $data['rating'] ?? null,
            'status' => CommentStatus::Pending,
            'is_anonymous' => false,
        ]);

        return back()->with('success', 'نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود.');
    }
}

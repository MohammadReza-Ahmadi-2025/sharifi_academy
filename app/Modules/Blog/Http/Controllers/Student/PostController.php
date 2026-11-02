<?php

namespace App\Modules\Blog\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Controllers\Concerns\ManagesPanelBlogPosts;

class PostController extends Controller
{
    use ManagesPanelBlogPosts;

    protected function blogInertiaPrefix(): string
    {
        return 'Student/Blog/Posts';
    }

    protected function blogRoutePrefix(): string
    {
        return 'student.posts';
    }

    protected function blogPanelMeta(): array
    {
        return [
            'base_path' => '/student/posts',
            'panel' => 'student',
        ];
    }
}

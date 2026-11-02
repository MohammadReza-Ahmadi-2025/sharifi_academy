<?php

namespace App\Modules\Blog\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Controllers\Concerns\ManagesPanelBlogPosts;

class PostController extends Controller
{
    use ManagesPanelBlogPosts;

    protected function blogInertiaPrefix(): string
    {
        return 'Instructor/Blog/Posts';
    }

    protected function blogRoutePrefix(): string
    {
        return 'instructor.posts';
    }

    protected function blogPanelMeta(): array
    {
        return [
            'base_path' => '/instructor/posts',
            'panel' => 'instructor',
        ];
    }
}

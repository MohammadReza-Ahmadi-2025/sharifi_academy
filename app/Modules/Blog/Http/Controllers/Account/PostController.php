<?php

namespace App\Modules\Blog\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Controllers\Concerns\ManagesPanelBlogPosts;

class PostController extends Controller
{
    use ManagesPanelBlogPosts;

    protected function blogInertiaPrefix(): string
    {
        return 'Account/Blog/Posts';
    }

    protected function blogRoutePrefix(): string
    {
        return 'account.posts';
    }

    protected function blogPanelMeta(): array
    {
        return [
            'base_path' => '/account/posts',
            'panel' => 'account',
        ];
    }
}

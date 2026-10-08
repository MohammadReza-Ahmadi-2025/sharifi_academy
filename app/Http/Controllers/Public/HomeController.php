<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Stories\Services\StoryFeedService;
use App\Services\Home\HomePageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, HomePageService $homePage, StoryFeedService $storyFeed): Response
    {
        $payload = $homePage->data();

        return Inertia::render('Public/Home', [
            ...$payload,
            'storyGroups' => $storyFeed->groupsForViewer($request->user()),
        ]);
    }
}

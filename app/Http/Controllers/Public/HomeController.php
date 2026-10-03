<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Home\HomePageService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(HomePageService $homePage): Response
    {
        $payload = $homePage->data();

        return Inertia::render('Public/Home', $payload);
    }
}

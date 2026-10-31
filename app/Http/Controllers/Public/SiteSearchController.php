<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Search\SiteSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSearchController extends Controller
{
    public function __construct(private readonly SiteSearchService $search) {}

    public function index(Request $request): Response
    {
        $q = (string) $request->query('q', '');
        $kind = $request->query('kind');
        $kind = is_string($kind) && $kind !== '' ? $kind : null;

        $allowedKinds = ['course', 'book', 'post', 'category', 'brand'];
        if ($kind !== null && ! in_array($kind, $allowedKinds, true)) {
            $kind = null;
        }

        $payload = $this->search->results($q, $kind);

        return Inertia::render('Public/Search/Index', [
            'q' => $payload['q'],
            'kind' => $kind,
            'total' => $payload['total'],
            'groups' => $payload['groups'],
            'kindOptions' => [
                ['value' => '', 'label' => 'همه'],
                ['value' => 'course', 'label' => 'دوره‌ها'],
                ['value' => 'book', 'label' => 'کتاب‌ها'],
                ['value' => 'post', 'label' => 'وبلاگ'],
                ['value' => 'category', 'label' => 'دسته‌ها'],
                ['value' => 'brand', 'label' => 'برندها'],
            ],
        ]);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $q = (string) $request->query('q', '');

        return response()->json([
            'items' => $this->search->autocomplete($q),
        ]);
    }
}

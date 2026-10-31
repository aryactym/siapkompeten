<?php

namespace App\Http\Controllers;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\DashboardRegistry;
use App\Http\Requests\DashboardFilterRequest;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    public function __construct(protected DashboardRegistry $registry)
    {
    }

    public function show(string $key, DashboardFilterRequest $request): JsonResponse
    {
        $widget = $this->registry->get($key);

        abort_if(! $widget instanceof Widget, 404);
        abort_unless($widget->authorize($request->user()), 403);

        $filter = DashboardFilter::fromRequest($request->validated());

        $result = $widget->data($filter);

        return response()->json($result->toArray())
            ->header('Cache-Control', 'private, max-age=300');
    }
}

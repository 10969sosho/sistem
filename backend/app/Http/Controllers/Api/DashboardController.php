<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service)
    {
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->summary(),
        ]);
    }
}

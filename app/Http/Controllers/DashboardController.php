<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function index(DashboardDataProvider $provider): View
    {
        return view('dashboard.index', [
            'datasetIds' => array_keys($provider->all()),
            'initialDataset' => 'sales',
        ]);
    }

    public function data(string $dataset, DashboardDataProvider $provider): JsonResponse
    {
        try {
            return response()->json(['dataset' => $dataset, ...$provider->get($dataset)]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}

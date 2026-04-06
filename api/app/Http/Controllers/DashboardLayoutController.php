<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiClient;
use App\Models\DashboardLayout;

class DashboardLayoutController extends Controller
{
    public function index(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        $apiClient = ApiClient::where('api_key', $apiKey)->first();

        if (!$apiClient) {
            return response()->json(['layout' => null], 200);
        }

        $layout = DashboardLayout::where('api_client_id', $apiClient->id)
            ->where('layout_name', 'default')
            ->first();

        return response()->json([
            'layout' => $layout ? $layout->layout_json : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'layout' => 'nullable|array',
        ]);

        $apiKey = $request->header('X-API-KEY');
        $apiClient = ApiClient::where('api_key', $apiKey)->first();

        if (!$apiClient) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $layout = $request->input('layout');

        DashboardLayout::updateOrCreate(
            [
                'api_client_id' => $apiClient->id,
                'layout_name' => 'default',
            ],
            [
                'layout_json' => $layout,
            ]
        );

        return response()->json(['message' => 'Layout saved']);
    }

    public function destroy(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        $apiClient = ApiClient::where('api_key', $apiKey)->first();

        if (!$apiClient) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DashboardLayout::where('api_client_id', $apiClient->id)
            ->where('layout_name', 'default')
            ->delete();

        return response()->json(['message' => 'Layout reset']);
    }
}


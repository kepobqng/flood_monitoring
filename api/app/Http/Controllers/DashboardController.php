<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\Device;
use App\Models\ActivityLog;
use App\Models\Command;

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        $chartLimit = min(max((int) $request->query('chart_limit', 40), 5), 120);

        $chartDevice = $request->query('chart_device');
        if ($chartDevice === null || $chartDevice === '') {
            $chartDevice = Device::orderBy('device_id')->value('device_id');
        }

        $chartReadings = collect();
        if ($chartDevice) {
            $chartReadings = SensorData::where('device_id', $chartDevice)
                ->latest()
                ->take($chartLimit)
                ->get()
                ->sortBy('created_at')
                ->values();
        }

        return response()->json([
            'devices'         => Device::orderBy('device_id')->get(),
            'latest_data'     => SensorData::latest()->take(100)->get(),
            'chart_readings'  => $chartReadings,
            'chart_device'    => $chartDevice,
            'commands'        => Command::latest()->take(10)->get(),
        ]);
    }

    public function log()
    {
        return response()->json(ActivityLog::latest()->take(30)->get());
    }
}
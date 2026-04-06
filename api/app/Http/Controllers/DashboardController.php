<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\Device;
use App\Models\ActivityLog;
use App\Models\Command;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        $chartLimit = min(max((int) $request->query('chart_limit', 40), 5), 120);
        $onlineTimeoutSeconds = min(max((int) $request->query('online_timeout', 30), 5), 600);
        
        // Ensure known simulator devices are always visible on dashboard
        foreach (['DEV001', 'DEV002', 'DEV003'] as $devId) {
            Device::firstOrCreate(
                ['device_id' => $devId],
                ['name' => $devId, 'location' => 'Unknown', 'status' => 'offline']
            );
        }

        $chartDevicesRaw = $request->query('chart_devices');
        $chartDeviceSingle = $request->query('chart_device');

        $chartDevices = [];
        if ($chartDevicesRaw !== null && $chartDevicesRaw !== '') {
            $chartDevices = array_values(array_filter(array_map('trim', explode(',', (string) $chartDevicesRaw))));
        } elseif ($chartDeviceSingle !== null && $chartDeviceSingle !== '') {
            $chartDevices = [(string) $chartDeviceSingle];
        } else {
            $first = Device::orderBy('device_id')->value('device_id');
            $chartDevices = $first ? [$first] : [];
        }

        $chartReadingsByDevice = [];
        foreach ($chartDevices as $devId) {
            $readings = SensorData::where('device_id', $devId)
                ->latest()
                ->take($chartLimit)
                ->get()
                ->sortBy('created_at')
                ->values();

            $chartReadingsByDevice[$devId] = $readings;
        }

        // Backward compatibility (single device)
        $chartDevice = $chartDevices[0] ?? null;
        $chartReadings = $chartDevice ? ($chartReadingsByDevice[$chartDevice] ?? collect()) : collect();

        $devices = Device::orderBy('device_id')->get();
        $lastSeenByDevice = SensorData::selectRaw('device_id, MAX(created_at) as last_seen_at')
            ->groupBy('device_id')
            ->pluck('last_seen_at', 'device_id');

        $onlineThreshold = now()->subSeconds($onlineTimeoutSeconds);
        $devices = $devices->map(function ($device) use ($lastSeenByDevice, $onlineThreshold) {
            $lastSeenAt = $lastSeenByDevice[$device->device_id] ?? null;
            $computedStatus = 'offline';
            $lastCommand = Command::where('device_id', $device->device_id)
                ->where('status', 'executed')
                ->latest('id')
                ->value('command');

            // Command state has priority so panel control feels immediate.
            if ($lastCommand === 'start') {
                $computedStatus = 'online';
            } elseif (in_array($lastCommand, ['stop', 'reset'], true)) {
                $computedStatus = 'offline';
            }

            // Fallback to telemetry heartbeat when there is no recent command state.
            if ($lastSeenAt !== null && $lastCommand === null) {
                $computedStatus = Carbon::parse($lastSeenAt)->gte($onlineThreshold) ? 'online' : 'offline';
            }

            $device->status = $computedStatus;
            $device->last_seen_at = $lastSeenAt;
            return $device;
        })->values();

        return response()->json([
            'devices'         => $devices,
            'latest_data'     => SensorData::latest()->take(100)->get(),
            'chart_readings'  => $chartReadings,
            'chart_device'    => $chartDevice,
            'chart_readings_by_device' => $chartReadingsByDevice,
            'commands'        => Command::latest()->take(10)->get(),
            'online_timeout'  => $onlineTimeoutSeconds,
        ]);
    }

    public function log()
    {
        return response()->json(ActivityLog::latest()->take(30)->get());
    }
}
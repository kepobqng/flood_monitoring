<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\Device;
use App\Models\ActivityLog;
use App\Models\Command;
use App\Models\WorkerStatus;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        $chartLimit = min(max((int) $request->query('chart_limit', 40), 5), 120);
        $onlineTimeoutSeconds = min(max((int) $request->query('online_timeout', 12), 5), 600);
        
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
            $lastCommandRow = Command::where('device_id', $device->device_id)
                ->where('status', 'executed')
                ->latest('id')
                ->first(['command', 'created_at']);
            $lastCommand = $lastCommandRow?->command;
            $lastCommandAt = $lastCommandRow?->created_at;

            $hasRecentTelemetry = $lastSeenAt !== null
                && Carbon::parse($lastSeenAt)->gte($onlineThreshold);
            $computedStatus = $hasRecentTelemetry ? 'online' : 'offline';

            // Keep immediate control feedback for start command.
            if ($lastCommand === 'start') {
                $computedStatus = 'online';
            } elseif (in_array($lastCommand, ['stop', 'reset'], true)) {
                // If there is new telemetry after stop/reset, device should be online again.
                if ($lastSeenAt !== null && $lastCommandAt !== null) {
                    $seenAfterStop = Carbon::parse($lastSeenAt)->gt(Carbon::parse($lastCommandAt));
                    $computedStatus = ($seenAfterStop && $hasRecentTelemetry) ? 'online' : 'offline';
                } else {
                    $computedStatus = 'offline';
                }
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
            'worker_statuses' => WorkerStatus::orderByDesc('last_heartbeat_at')->take(30)->get(),
            'online_timeout'  => $onlineTimeoutSeconds,
        ]);
    }

    public function log()
    {
        return response()->json(ActivityLog::latest()->take(30)->get());
    }
}
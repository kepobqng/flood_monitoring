<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\Device;
use App\Models\ActivityLog;

class SensorController extends Controller
{
    public function ingest(Request $request)
    {
        $data = $request->validate([
            'device_id'   => 'required|string',
            'water_level' => 'required|numeric',
            'rainfall'    => 'required|numeric',
        ]);

        $alert = 'normal';
        if ($data['water_level'] > 200) $alert = 'danger';
        elseif ($data['water_level'] >= 101) $alert = 'warning';

        $data['alert_level'] = $alert;

        SensorData::create($data);

        Device::updateOrCreate(
            ['device_id' => $data['device_id']],
            ['status' => 'online', 'name' => $data['device_id'], 'location' => 'Unknown']
        );

        ActivityLog::create([
            'device_id' => $data['device_id'],
            'action'    => 'data_received',
            'detail'    => "Water: {$data['water_level']}cm, Rain: {$data['rainfall']}mm/h, Alert: $alert"
        ]);

        return response()->json(['message' => 'Data saved', 'alert_level' => $alert]);
    }

    public function index()
    {
        return response()->json(SensorData::latest()->take(50)->get());
    }
}
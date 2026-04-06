<?php

namespace App\Http\Controllers;

use App\Models\WorkerStatus;
use Illuminate\Http\Request;

class WorkerStatusController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'worker_id' => 'required|string|max:120',
            'device_id' => 'nullable|string|max:120',
            'status' => 'required|string|max:50',
            'message' => 'nullable|string|max:1000',
        ]);

        $status = WorkerStatus::updateOrCreate(
            [
                'worker_id' => $data['worker_id'],
                'device_id' => $data['device_id'] ?? null,
            ],
            [
                'status' => $data['status'],
                'message' => $data['message'] ?? null,
                'last_heartbeat_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Worker status updated',
            'id' => $status->id,
        ]);
    }
}

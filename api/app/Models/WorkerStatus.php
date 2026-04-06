<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerStatus extends Model
{
    protected $fillable = [
        'worker_id',
        'device_id',
        'status',
        'message',
        'last_heartbeat_at',
    ];
}

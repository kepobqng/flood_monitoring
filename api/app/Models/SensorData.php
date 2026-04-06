<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
   protected $fillable = ['device_id', 'water_level', 'rainfall', 'alert_level'];
}

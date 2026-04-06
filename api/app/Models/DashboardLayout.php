<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $fillable = ['api_client_id', 'layout_name', 'layout_json'];

    protected $casts = [
        'layout_json' => 'array',
    ];
}


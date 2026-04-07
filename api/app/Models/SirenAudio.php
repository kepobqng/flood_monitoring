<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SirenAudio extends Model
{
    protected $table = 'siren_audios';

    protected $fillable = [
        'api_client_id',
        'file_name',
        'mime_type',
        'audio_base64',
    ];
}


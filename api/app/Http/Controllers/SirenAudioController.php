<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use App\Models\SirenAudio;
use Illuminate\Http\Request;

class SirenAudioController extends Controller
{
    private function resolveApiClient(Request $request): ?ApiClient
    {
        $apiKey = $request->header('X-API-KEY');
        if (!$apiKey) {
            return null;
        }
        return ApiClient::where('api_key', $apiKey)->first();
    }

    public function show(Request $request)
    {
        $apiClient = $this->resolveApiClient($request);
        if (!$apiClient) {
            return response()->json(['audio' => null], 200);
        }

        $audio = SirenAudio::where('api_client_id', $apiClient->id)->first();
        if (!$audio) {
            return response()->json(['audio' => null], 200);
        }

        return response()->json([
            'audio' => [
                'file_name' => $audio->file_name,
                'mime_type' => $audio->mime_type,
                'data_url' => 'data:' . $audio->mime_type . ';base64,' . $audio->audio_base64,
                'updated_at' => $audio->updated_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $apiClient = $this->resolveApiClient($request);
        if (!$apiClient) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $hasFile = $request->hasFile('audio');
        $isBase64 = $request->filled('audio_base64');

        if (!$hasFile && !$isBase64) {
            return response()->json(['error' => 'Audio tidak ditemukan'], 422);
        }

        $raw = null;
        $fileName = 'siren.mp3';
        $mimeType = 'audio/mpeg';

        if ($hasFile) {
            $request->validate([
                'audio' => 'required|file|mimes:mp3|max:10240',
            ]);
            $file = $request->file('audio');
            $raw = file_get_contents($file->getRealPath());
            $fileName = $file->getClientOriginalName() ?: 'siren.mp3';
            $mimeType = $file->getMimeType() ?: 'audio/mpeg';
        } else {
            $request->validate([
                'audio_base64' => 'required|string|min:32',
                'file_name' => 'nullable|string|max:255',
                'mime_type' => 'nullable|string|max:100',
            ]);
            $raw = base64_decode((string) $request->input('audio_base64'), true);
            $fileName = (string) ($request->input('file_name') ?: 'siren.mp3');
            $mimeType = (string) ($request->input('mime_type') ?: 'audio/mpeg');
        }

        if ($raw === false || $raw === null || strlen($raw) === 0) {
            return response()->json(['error' => 'Data audio tidak valid'], 422);
        }

        $record = SirenAudio::updateOrCreate(
            ['api_client_id' => $apiClient->id],
            [
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'audio_base64' => base64_encode($raw),
            ]
        );

        return response()->json([
            'message' => 'Audio sirene disimpan',
            'audio' => [
                'file_name' => $record->file_name,
                'mime_type' => $record->mime_type,
                'data_url' => 'data:' . $record->mime_type . ';base64,' . $record->audio_base64,
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        $apiClient = $this->resolveApiClient($request);
        if (!$apiClient) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        SirenAudio::where('api_client_id', $apiClient->id)->delete();
        return response()->json(['message' => 'Audio sirene dihapus']);
    }
}


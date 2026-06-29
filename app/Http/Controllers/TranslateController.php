<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TranslateController extends Controller
{
    /**
     * Proxy a single string to the self-hosted LibreTranslate instance.
     * Keeps the service URL/key server-side and avoids browser CORS.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:20000'],
            'source' => ['required', 'in:en,id'],
            'target' => ['required', 'in:en,id', 'different:source'],
            'format' => ['in:text,html'],
        ]);

        $payload = [
            'q' => $data['q'],
            'source' => $data['source'],
            'target' => $data['target'],
            'format' => $data['format'] ?? 'text',
        ];

        // ponytail: api_key only sent when the instance requires one
        if ($key = config('services.libretranslate.key')) {
            $payload['api_key'] = $key;
        }

        try {
            $res = Http::asForm()->timeout(20)->post(
                rtrim(config('services.libretranslate.url'), '/').'/translate',
                $payload
            );
        } catch (ConnectionException) {
            return response()->json(['message' => 'Layanan terjemahan tidak tersedia.'], 502);
        }

        if ($res->failed()) {
            return response()->json(['message' => 'Layanan terjemahan tidak tersedia.'], 502);
        }

        return response()->json(['translatedText' => $res->json('translatedText', '')]);
    }
}

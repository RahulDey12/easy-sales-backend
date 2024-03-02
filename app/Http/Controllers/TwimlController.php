<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Url\Url;
use Twilio\TwiML\VoiceResponse;

class TwimlController extends Controller
{
    public function __invoke(VoiceResponse $voiceResponse): Response
    {
        $url = Url::fromString(config('app.url'))
            ->withAllowedSchemes(['wss'])
            ->withScheme('wss')
            ->withPath('ws');

        $voiceResponse->connect()->stream([
            'url' => (string) $url,
        ]);

        return response(content: $voiceResponse->asXML(), headers: [
            'Content-Type' => 'application/xml',
        ]);
    }
}

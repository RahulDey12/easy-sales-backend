<?php

namespace App\Call\Transcriptions;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use Spatie\Url\SchemeValidator;
use Spatie\Url\Url;
use function Ratchet\Client\connect;

class AssemblyAIRealTime implements EventEmitterInterface
{
    use EventEmitterTrait;

    private const API_BASE = 'https://api.assemblyai.com/v2/realtime';

    protected bool $transcriptionStarted = false;

    protected WebSocket $wsConnection;

    public function __construct(protected EventEmitterInterface $voiceStream)
    {
    }

    public function connect()
    {
//        if(! $this->transcriptionStarted) {
//
//        }

        connect($this->getWsUrl())->then(function (WebSocket $conn) {
            $this->wsConnection = $conn;
            $this->transcriptionStarted = true;

            $this->voiceStream->on('data', function (string $voiceData) use ($conn) {
                $conn->send(json_encode([
                    'audio_data' => $voiceData
                ]));
            });

            $conn->on('message', function (Message $msg) {
                $msgData = json_decode($msg->getPayload(), true);

                if(! Arr::has($msgData, 'message_type') || $msgData['message_type'] !== 'FinalTranscript') {
                    return;
                }

                $this->emit('transcribe', [$msgData['text']]);
            });
        });
    }

    public function close(): void
    {
        if(! isset($this->wsConnection)) {
            return;
        }

        $this->wsConnection->close();
    }

    protected function createTempToken()
    {
        $resp = Http::withHeaders([
            'authorization' => config('services.assembly_ai.key'),
        ])->post($this->getTokenUrl(), [
            'expires_in' => 360000,
        ]);

        return $resp->json()['token'];
    }

    protected function getTokenUrl(): string
    {
        $url = $this->getBaseUrl();
        $path = $url->getPath();

        $path .= '/token';

        return (string) $url->withPath($path);
    }

    protected function getWsUrl(): string
    {
        $url = $this->getBaseUrl();
        $path = $url->getPath();

        $path .= '/ws';

        $token = $this->createTempToken();

        $url = $url->withQueryParameters([
            'sample_rate' => 8000,
            'encoding' => 'pcm_mulaw',
            'token' => $token,
        ])->withScheme('wss');

        return (string) $url->withPath($path);
    }

    protected function getBaseUrl(): Url
    {
        return Url::fromString(self::API_BASE)
            ->withAllowedSchemes([...SchemeValidator::VALID_SCHEMES, 'wss']);
    }
}

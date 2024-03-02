<?php

namespace App\Call;

use Closure;
use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Illuminate\Support\Arr;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use React\Promise\PromiseInterface;
use Spatie\Url\SchemeValidator;
use Spatie\Url\Url;
use function Ratchet\Client\connect;

class TextToSpeech implements EventEmitterInterface
{
    use EventEmitterTrait;

    private const API_BASE = 'https://api.elevenlabs.io/v1/text-to-speech';

    protected EventEmitter $stream;

    protected bool $connected = false;

    public function __construct(public readonly string $voiceId, public readonly string $modelId = 'eleven_turbo_v2')
    {
        $this->stream = new EventEmitter();
    }

    public function connect(): PromiseInterface
    {
        return connect($this->getWsUrl())->then(function (WebSocket $conn) {
            $this->connected = true;
            dump('new connection');

            $bosMessage = [
                'text'=> " ",
                'voice_settings'=> [
                    'stability'=> 0.5,
                    'similarity_boost'=> true,
                ],
                'generation_config'=> [
                    'chunk_length_schedule'=> [120, 160, 250, 290],
                ],
                'xi_api_key'=> config('services.xi_labs.key'),
            ];

            $eosMessage = [
                'text' => '',
            ];

            $conn->send(json_encode($bosMessage));

            $this->stream->on('text', function (Closure $textChunkIterator) use ($eosMessage, $conn) {
                foreach ($textChunkIterator() as $text) {
                    $conn->send(json_encode(compact('text')));
                }

                $conn->send(json_encode($eosMessage));
            });

            $conn->on('close', function() use ($conn) {
                $this->connected = false;
                $this->stream->removeAllListeners();
                $conn->removeAllListeners();
            });

            $conn->on('message', function (Message $msg) {
                $response = json_decode($msg->getPayload(), true);

                if(! Arr::has($response, 'audio')) {
                    return;
                }

                if(Arr::get($response, 'isFinal')) {
                    $this->emit('final');
                }

                if(is_null(Arr::get($response, 'audio'))) {
                    return;
                }

                $this->emit('audio', [$response['audio']]);
            });
        });
    }

    public function write(Closure $textChunkIterator): void
    {
        $this->stream->emit('text', [$textChunkIterator]);
    }

    public function end(): void
    {
        $this->stream->emit('end');
    }

    public function getConnected(): bool
    {
        return $this->connected;
    }

    protected function getWsUrl(): string
    {
        $url = $this->getBaseUrl();
        $path = $url->getPath();

        $path = "{$path}/{$this->voiceId}/stream-input";

        $url = $url->withQueryParameters([
            'model_id' => $this->modelId,
            'optimize_streaming_latency' => 4,
            'output_format' => 'ulaw_8000',
        ])->withScheme('wss');

        return (string) $url->withPath($path);
    }

    protected function getBaseUrl(): Url
    {
        return Url::fromString(self::API_BASE)
            ->withAllowedSchemes([...SchemeValidator::VALID_SCHEMES, 'wss']);
    }
}

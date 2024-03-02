<?php

namespace App\Call;

use App\Call\Transcriptions\AssemblyAIRealTime;
use Closure;
use Illuminate\Support\Arr;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class CallServer implements MessageComponentInterface
{
    protected AssemblyAIRealTime $transcriber;

    protected VoiceEmitter $voiceEmitter;

    protected Assistant $assistant;

    protected TextToSpeech $tts;

    protected string $streamSid;

    public function onOpen(ConnectionInterface $conn)
    {
        $this->voiceEmitter = new VoiceEmitter();
        $this->assistant = new Assistant();

        $this->transcriber = new AssemblyAIRealTime($this->voiceEmitter);
        $this->transcriber->connect();

        $this->tts = new TextToSpeech('XrExE9yKIg1WjnnlVkGX');
        $this->tts->connect();

        $this->transcriber->on('transcribe', function (string $transcription) {
            $this->voiceEmitter->pause();

            if(! $this->tts->getConnected()) {
                $this->tts->connect()->then(function () use ($transcription) {
                    $this->assistant->sendUserMessage($transcription);
                });

                return;
            }

            $this->assistant->sendUserMessage($transcription);
        });


        $this->assistant->on('speak', function (Closure $messageIterator) {
            $this->tts->write($messageIterator);
        });

        $this->tts->on('audio', function (string $audio) use ($conn) {
            $conn->send(json_encode([
                'event' => 'media',
                'streamSid' => $this->streamSid,
                'media' => [
                    'payload' => $audio,
                ],
            ]));
        });

        $this->tts->on('final', fn () => $this->voiceEmitter->resume());

        $this->assistant->on('hang_up', function () {
            dump("Hang Up");
        });
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->transcriber->close();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $response = json_decode($msg->getPayload(), true);

        if($response['event'] === 'start') {
            $this->streamSid = Arr::get($response, 'start.streamSid');
        }

        if($response['event'] !== 'media') {
            return;
        }

        $this->voiceEmitter->send($response['media']['payload']);
    }
}

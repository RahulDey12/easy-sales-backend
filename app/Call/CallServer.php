<?php

namespace App\Call;

use App\Call\Transcriptions\AssemblyAIRealTime;
use App\Models\Call;
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

    protected Call $call;

    public function onOpen(ConnectionInterface $conn)
    {
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if(isset($this->transcriber)) {
            $this->transcriber->close();
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $response = json_decode($msg->getPayload(), true);

        if($response['event'] === 'start') {
            $this->streamSid = Arr::get($response, 'start.streamSid');
//            $this->playRingtone($conn);

            $call = Call::where('twilio_sid', Arr::get($response, 'start.callSid'))->first();

            if(is_null($call)) {
                $conn->close();
                return;
            }
            $this->call = $call;

            $this->startSession($conn);
        }

        if($response['event'] !== 'media') {
            return;
        }

        $this->voiceEmitter->send($response['media']['payload']);
    }

    protected function playRingtone(ConnectionInterface $conn): void
    {
        $ringtoneContents = file_get_contents(storage_path('/app/call/ring.wav'));

        $conn->send(json_encode([
            'event' => 'media',
            'streamSid' => $this->streamSid,
            'media' => [
                'payload' => base64_encode($ringtoneContents),
            ],
        ]));
    }

    protected function startSession(ConnectionInterface $conn)
    {
        $this->voiceEmitter = new VoiceEmitter();
        $this->assistant = new Assistant();

        $this->transcriber = new AssemblyAIRealTime($this->voiceEmitter);
        $this->transcriber->connect();

        $this->tts = new TextToSpeech($this->call->caller->xi_voice_id);
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
                'event' => 'clear',
                'streamSid' => $this->streamSid,
            ]));

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
}

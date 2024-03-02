<?php

namespace App\Console\Commands;

use App\Call\TextToSpeech;
use App\Call\Transcriptions\AssemblyAIRealTime;
use Illuminate\Console\Command;
use Twilio\Rest\Client;

class PlaceCall extends Command
{
    protected $signature = 'app:call';

    protected $description = 'Command description';

    public function handle(): int
    {
//        $assembly = new AssemblyAIRealTime();
//        $assembly->connect();

//        $tts = new TextToSpeech('XrExE9yKIg1WjnnlVkGX');
//        $tts->connect();

        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));

        $call = $twilio->calls->create('+917003844595', config('services.twilio.phone_number'), [
            'url' => route('api:twiml'),
        ]);

        $this->components->info("Call Placed SID: [{$call->sid}]");


        return self::SUCCESS;
    }
}

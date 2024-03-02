<?php

namespace App\Jobs;

use App\Models\Call;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;

class PlaceCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Call $call)
    {
        //
    }

    public function handle(Client $twilio): void
    {
        $twilioCall = $twilio->calls->create($this->call->phone_number, config('services.twilio.phone_number'), [
            'url' => route('api:twiml'),
        ]);

        $this->call->update([
            'twilio_sid' => $twilioCall->sid,
        ]);
    }
}

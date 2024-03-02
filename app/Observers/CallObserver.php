<?php

namespace App\Observers;

use App\Jobs\PlaceCall;
use App\Models\Call;

class CallObserver
{
    public function created(Call $call): void
    {
        PlaceCall::dispatch($call)->onQueue('calls');
    }
}

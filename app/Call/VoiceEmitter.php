<?php

namespace App\Call;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;

class VoiceEmitter implements EventEmitterInterface
{
    use EventEmitterTrait;

    protected bool $isPaused = false;

    public function send(string $voiceData)
    {
        if ($this->isPaused) {
            return;
        }

        $this->emit('data', [$voiceData]);
    }

    public function pause(): void
    {
        $this->isPaused = true;
    }

    public function resume(): void
    {
        $this->isPaused = false;
    }
}

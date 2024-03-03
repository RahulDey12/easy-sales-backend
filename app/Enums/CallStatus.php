<?php

namespace App\Enums;

enum CallStatus: string
{
    case QUEUED = 'queued';
    case CALLING = 'calling';
    case FINISHED = 'finished';
}

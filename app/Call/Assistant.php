<?php

namespace App\Call;

use Closure;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponseChoice;

class Assistant implements EventEmitterInterface
{
    use EventEmitterTrait;

    private const MODEL = 'mixtral-8x7b-32768';

    protected Client $client;

    protected array $messages;

    protected string $tmpMessage = '';

    public function __construct()
    {
        $this->client = OpenAI::factory()
            ->withApiKey(config('services.groq.key'))
            ->withBaseUri('https://api.groq.com/openai/v1/')
            ->make();

        $this->addMessage('system', Blade::render('ai_templates.assistant-system-message'));
    }

    public function sendUserMessage(string $message)
    {
        $messageIterator = $this->addUserMessage($message)->send();

        $this->emit('speak', [$messageIterator]);
    }

    protected function addMessage(string $role, string $content): static
    {
        $this->messages[] = compact('role', 'content');

        return $this;
    }

    protected function addUserMessage(string $message): static
    {
        $this->addMessage('user', $message);

        return $this;
    }

    protected function addAssistantMessage(string $message): static
    {
        $this->addMessage('assistant', $message);

        return $this;
    }

    protected function send(): Closure
    {

        $stream = $this->client->chat()->createStreamed([
            'model' => self::MODEL,
            'messages' => $this->messages,
        ]);

        return function () use ($stream) {
            foreach ($stream as $response) {
                /** @var OpenAI\Responses\Chat\CreateStreamedResponseChoice $choice */
                $choice = $response->choices[0];
                $text = $choice->delta->content;

                $this->tmpMessage .= $text;

                if(! is_null($choice->finishReason)) {
                    $this->addAssistantMessage($this->tmpMessage);
                    $this->tmpMessage = '';
                }

                if(is_null($text) || $text === '') {
                    continue;
                }

                yield $text;
            }
        };
    }
}

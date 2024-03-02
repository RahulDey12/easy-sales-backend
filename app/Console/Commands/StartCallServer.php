<?php

namespace App\Console\Commands;

use App\Call\CallServer;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class StartCallServer extends Command
{
    protected $signature = 'ws-serve {--host=0.0.0.0} {--port=8080}';

    protected $description = 'Start the websocket server.';

    public function handle(): int
    {
        $this->components->info(
            "Websocket Server running on [ws://{$this->option('host')}:{$this->option('port')}]"
        );

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new CallServer()
                )
            ),
            $this->option('port'),
            $this->option('host'),
        );

        $server->run();

        return self::SUCCESS;
    }
}

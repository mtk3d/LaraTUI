<?php

namespace LaravelSailTui\Commands;

use LaravelSailTui\DockerClient\Docker;
use LaravelSailTui\Logger;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ResponseException;

class SailContainersCommand extends Command
{
    public static string $commandName = 'sail_containers_command';
    private readonly Docker $docker;

    public function init(): void
    {
        $this->state->set('containers_info', 'Loading...');
        $this->docker = new Docker();

        $this->docker->networks()->then(function (ResponseInterface $response) {
            $networks = json_decode((string)$response->getBody());
            foreach ($networks as $network) {
                if (str_ends_with($network->Name, 'sail')) {
                    $this->state->set('sail_network_id', $network->Id);
                    break;
                }
            }
        });
    }

    public function execute(array $data): void
    {
        $filter = [
            'network' => [
                $this->state->get('sail_network_id'),
            ],
        ];

        $this->docker->containers($filter)
            ->then(function (ResponseInterface $response) {
                $containers = json_decode((string)$response->getBody());
                $statuses = [];
                foreach ($containers as $container) {
                    foreach ($container->Names as $name) {
                        $statuses[$name] = $container->Status;
                    }
                }

                $this->state->set('services_status', $statuses);
            }, function (ResponseException $e) {
                echo 'Error: ' . $e->getMessage() . PHP_EOL;
            });
    }
}

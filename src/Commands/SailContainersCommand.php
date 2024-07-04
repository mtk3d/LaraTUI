<?php

namespace LaraTui\Commands;

use LaraTui\DockerClient\Docker;
use LaraTui\State;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ResponseException;

class SailContainersCommand extends Command
{
    public function __invoke(State $state): void
    {
        $state->set('containers_info', 'Loading...');
        $docker = new Docker();

        $docker->networks()->then(function (ResponseInterface $response) use ($state) {
            $networks = json_decode((string) $response->getBody());
            foreach ($networks as $network) {
                if (str_ends_with($network->Name, 'sail')) {
                    $state->set('sail_network_id', $network->Id);
                    break;
                }
            }
        });

        $filter = [
            'network' => [
                $state->get('sail_network_id'),
            ],
        ];

        $docker->containers($filter)
            ->then(function (ResponseInterface $response) use ($state) {
                $containers = json_decode((string) $response->getBody());
                $statuses = [];
                foreach ($containers as $container) {
                    foreach ($container->Names as $name) {
                        $statuses[$name] = $container->Status;
                    }
                }

                $state->set('services_status', $statuses);
            }, function (ResponseException $e) {
                echo 'Error: '.$e->getMessage().PHP_EOL;
            });
    }
}

<?php

namespace LaraTui\Commands;

use LaraTui\State\LaravelVersions;
use React\Http\Message\Response;

class FetchLaravelVersionsCommand extends Command
{
    const string LARAVEL_VERSIONS_URL = 'https://laravelversions.com/api/versions';

    public function execute(array $data): void
    {
        $this->browser
            ->get(self::LARAVEL_VERSIONS_URL)
            ->then(function (Response $response) {
                $body = $response->getBody()->getContents();
                $this->state->set(
                    LaravelVersions::class,
                    LaravelVersions::fromResponseBody($body),
                );
            });
    }
}

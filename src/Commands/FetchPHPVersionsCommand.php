<?php

namespace LaraTui\Commands;

use LaraTui\State\PHPVersions;
use React\Http\Message\Response;

class FetchPHPVersionsCommand extends Command
{
    const string PHP_VERSIONS_URL = 'https://php.watch/api/v1/versions';

    public function execute(array $data): void
    {
        $this->browser->get(self::PHP_VERSIONS_URL)
            ->then(function (Response $response) {
                $body = $response->getBody()->getContents();
                $this->state->set(PHPVersions::class, PHPVersions::fromResponseBody($body));
            });
    }
}

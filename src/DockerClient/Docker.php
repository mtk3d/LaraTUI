<?php

declare(strict_types=1);

namespace LaraTui\DockerClient;

use React\Http\Browser;
use React\Promise\PromiseInterface;
use React\Socket\FixedUriConnector;
use React\Socket\UnixConnector;

readonly class Docker
{
	private const string URL = 'http://v1.14';
	private const string NETWORKS = '/networks';
	private const string CONTAINERS = '/containers/json';

	private Browser $browser;

	public function __construct()
	{
		$connector = new FixedUriConnector(
			'unix:///Users/mateuszcholewka/.colima/default/docker.sock',
			new UnixConnector()
		);
		$this->browser = new Browser($connector);
	}

	public function networks(): PromiseInterface
	{
		return $this->browser->get(
			$this->buildUrl(self::NETWORKS),
		);
	}

	public function containers(array $filters = null): PromiseInterface
	{
		return $this->browser->get(
			$this->buildUrl(
				self::CONTAINERS,
				['filters' => $this->parseFilters($filters)],
			),
		);
	}

	private function buildUrl(string $endpoint, array $query = []): string
	{
		$query = http_build_query($query);
		return self::URL . "$endpoint?$query";
	}

	private function parseFilters(array $filters): string
	{
		if (!$filters) {
			return '';
		}

		return (json_encode($filters));
	}
}

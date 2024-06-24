<?php

use LaraTui\State\LaravelVersions;

describe('test laravel versions state', function () {
    test('can be build from response body', function () {
        $body = '
          {
            "data": [
              {
                "major": 11,
                "latest_minor": 11,
                "latest_patch": 1,
                "latest": "11.11.1",
                "released_at": "2024-03-12T00:00:00.000000Z",
                "ends_bugfixes_at": "2025-08-05T00:00:00.000000Z",
                "ends_securityfixes_at": "2026-02-03T00:00:00.000000Z",
                "supported_php": [
                  "8.2",
                  "8.3"
                ],
                "status": "active",
                "links": [
                  {
                    "type": "GET",
                    "rel": "self",
                    "href": "https://laravelversions.com/api/versions/11"
                  },
                  {
                    "type": "GET",
                    "rel": "latest",
                    "href": "https://laravelversions.com/api/versions/11.11.1"
                  }
                ],
                "global": {
                  "latest_version": "11.11.1"
                }
              },
              {
                "major": 10,
                "latest_minor": 48,
                "latest_patch": 14,
                "latest": "10.48.14",
                "released_at": "2023-02-14T00:00:00.000000Z",
                "ends_bugfixes_at": "2024-08-07T00:00:00.000000Z",
                "ends_securityfixes_at": "2025-02-07T00:00:00.000000Z",
                "supported_php": [
                  "8.1",
                  "8.2",
                  "8.3"
                ],
                "status": "active",
                "links": [
                  {
                    "type": "GET",
                    "rel": "self",
                    "href": "https://laravelversions.com/api/versions/10"
                  },
                  {
                    "type": "GET",
                    "rel": "latest",
                    "href": "https://laravelversions.com/api/versions/10.48.14"
                  }
                ],
                "global": {
                  "latest_version": "11.11.1"
                }
              }
            ]
          }
        ';

        $laravelVersions = LaravelVersions::fromResponseBody($body);

        expect($laravelVersions->data[0]->major)->toBe(11);
        expect($laravelVersions->data[0]->latest)->toBe('11.11.1');
        expect($laravelVersions->data[0]->status)->toBe('active');
    });
});

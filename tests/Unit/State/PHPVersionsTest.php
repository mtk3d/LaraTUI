<?php

use LaraTui\State\PHPVersions;

describe('test php versions state', function () {
    test('can be build from response body', function () {
        $body = '
            {
              "data": {
                "80300": {
                  "versionId": 80300,
                  "name": "8.3",
                  "releaseDate": null,
                  "isEOLVersion": false,
                  "isSecureVersion": false,
                  "isLatestVersion": false,
                  "isFutureVersion": true,
                  "isNextVersion": true,
                  "statusLabel": "Upcoming Release"
                },
                "80200": {
                  "versionId": 80200,
                  "name": "8.2",
                  "releaseDate": "2022-12-08",
                  "isEOLVersion": false,
                  "isSecureVersion": true,
                  "isLatestVersion": true,
                  "isFutureVersion": false,
                  "isNextVersion": false,
                  "statusLabel": "Supported (Latest)"
                }
              },
              "_documentation": "https:\/\/php.watch\/api#versions",
              "_description": "Latest PHP versions, along with their support\/security\/EOL status and release\/EOL\/active dates."
            }
        ';

        $phpVersions = PHPVersions::fromResponseBody($body);

        expect($phpVersions->data[0]->name)->toBe('8.3');
        expect($phpVersions->data[1]->name)->toBe('8.2');
        expect($phpVersions->data[0]->statusLabel)->toBe('Upcoming Release');
        expect($phpVersions->data[1]->statusLabel)->toBe('Supported (Latest)');
    });
});

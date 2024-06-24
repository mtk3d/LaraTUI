<?php

use LaraTui\State\MigrationStatus;

describe('test migration status state', function () {
    test('can be build from artisan migrate status command', function () {
        $output = <<<TEXT

          Migration name .............................................. Batch / Status  
          0001_01_01_000000_create_users_table ............................... [1] Ran  
          0001_01_01_000001_create_cache_table ............................... [1] Ran  
          0001_01_01_000002_create_jobs_table ................................ [1] Ran  
          2024_06_12_210400_add_two_factor_columns_to_users_table ............ [1] Ran  
          2024_06_12_210411_create_personal_access_tokens_table .............. [1] Ran  
          2024_06_12_210411_create_teams_table ............................... [1] Ran  
          2024_06_12_210412_create_team_user_table ........................... [1] Ran  
          2024_06_12_210413_create_team_invitations_table .................... [1] Ran  
          2024_06_24_201742_add_test_migration ............................... Pending  

        TEXT;

        $migrationStatus = MigrationStatus::fromMigrationStatusCommand($output);

        expect($migrationStatus->all)->toBe(9);
        expect($migrationStatus->pending)->toBe(1);
    });
});

# LaraTUI

LaraTUI is terminal user interface for laravel local environment

![Interface](./assets/interface.png)

Project is something between PoC and very Alpha state for now. There are no tests, some parts are mocked, and it needs some refactoring.

## Requirements

- PHP >= 8.2
- Composer
- laravel/sail with docker
- NerdFont

## Todo

- [x] prepare framework using reactphp event loop
- [x] read status of docker containers
- [x] read status of composer packages
- [x] run docker compose up and read logs in app
- [x] run migrations and handle output of it
- [ ] start using php-tui events reading
- [ ] PHP and Composer versions check
- [ ] LaraTUI config to keep status of all project
- [ ] update functionality
- [ ] automatic help popup on `?`
- [ ] envs view
- [ ] running artisan commands with quick commands
- [ ] check migration status
- [ ] support other envs than laravel sail, laradock/vault
- [ ] implement composer audit execution

# LaraTUI

LaraTUI is terminal user interface for laravel local environment

![Interface](./assets/interface.gif)

Project state is something around PoC and pre-Alpha. Some parts are mocked, it's flaky, and it needs refactoring and more tests.

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
- [x] start using php-tui events reading
- [x] PHP and Composer versions check
- [x] check migration status
- [x] logs view
- [x] running artisan commands
- [ ] achieve 70% tests coverage
- [ ] add more info about versions updating
- [ ] LaraTUI config to keep status of all projects
- [ ] update packages functionality
- [ ] automatic help popup on `?`
- [ ] envs view
- [ ] support other envs than laravel sail like laradock or vault
- [ ] implement project creating window
- [ ] prepare phar and allow to install using composer
- [ ] support no nerdfont
- [ ] implement composer audit execution

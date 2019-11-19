# Changelog

## v0.0.4 - planned
Add project management

## v0.0.3
Added reset filters options to employee index table.
Fixed Bug Report HT-357
Fixed Bug Report HT-359
Optimized database query in payments index


## v0.0.2
Added react based table for employee index.
Added consumer to handle asynchronous recalculation of skill stats when employee payments get updated.

## v0.0.1
Installed symfony 4.2
Installed rabbitmq-bundle 1.14
Installed php-cs-fixer 2.13
Installed webpack-encore-bundle 1.0

Added initial docker-compose setup with:
* php:7.2.13-fpm with extra extensions (pdo_mysql, mbstring, bcmath, zip, xml)
* nginx:1.15.7
* mariadb:10.4.0-bionic
* rabbitmq:3.7.8-management
* node:8.14.0-alpine for react


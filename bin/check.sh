#!/usr/bin/env bash

composer install --no-interaction

composer php:lint
composer phpstan
composer psalm
composer rector

compose security-check

./vendor/bin/phpunit --no-coverage

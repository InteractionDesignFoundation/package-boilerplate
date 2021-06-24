#!/usr/bin/env bash

set -xe

composer install --no-interaction && \
composer php:lint && \
composer phpstan && \
composer psalm && \
composer rector && \
composer security:check && \
composer test

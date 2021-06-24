# Template code to create a composer package

This repository contains the boilerplate/template code to create a composer package.
It contains:
1. PHPUnit and PEST PHP
2. A docker container (docker file + docker compose) with php 8 cli to run tests / linters.
3. Static code analyzers (psalm, phpcs, phpcpd, phpstan) and the basic config
4. Rector (with some basic ruleset)
5. Composer scripts to run linters + tests easier
6. Simple Github Actions workflows to run tests / linters on push

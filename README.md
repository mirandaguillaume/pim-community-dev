# Akeneo PIM Development Repository

[![CI](https://github.com/mirandaguillaume/pim-community-dev/actions/workflows/ci.yml/badge.svg)](https://github.com/mirandaguillaume/pim-community-dev/actions/workflows/ci.yml)
[![Code Metrics](https://img.shields.io/badge/AST--Metrics-monitored-blue?logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiPjxwYXRoIGQ9Ik0zIDNoMTh2MThIM3oiLz48cGF0aCBkPSJNMyAxN2w2LTYgNC00IDgtOCIvPjwvc3ZnPg==)](https://github.com/mirandaguillaume/pim-community-dev/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen?logo=php)](https://phpstan.org/)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-purple?logo=php)](https://www.php.net/releases/8.4/en.php)
[![Symfony 6.4](https://img.shields.io/badge/Symfony-6.4%20LTS-black?logo=symfony)](https://symfony.com/)
[![Crowdin](https://d322cqt584bo4o.cloudfront.net/akeneo/localized.svg)](https://crowdin.com/project/akeneo)

Welcome to Akeneo PIM Product.

This repository is used to develop the Akeneo PIM product.

Practically, it means the Akeneo PIM source code is present in the src/ directory.

**If you want to create a new PIM project based on Akeneo PIM, please use https://www.github.com/akeneo/pim-community-standard**

If you want to contribute to the Akeneo PIM (and we will be pleased if you do!), you can fork this repository and submit a pull request.

## Application Technical Information

The following documentation is designed for both clients and partners and provides all technical information required to define required server(s) to run Akeneo PIM application and check that end users workstation is compatible with Akeneo PIM application:
https://docs.akeneo.com/master/install_pim/manual/system_requirements/system_requirements.html

## Installation instructions

To install Akeneo PIM for a PIM project or for evaluation, please follow:
https://docs.akeneo.com/master/install_pim/index.html

### Build the Docker image for local development

```bash
docker build --target dev -t akeneo/pim-php-dev:master .
```

## Upgrade instructions

To upgrade Akeneo PIM to a newer version, please follow:
https://docs.akeneo.com/master/migrate_pim/index.html

## Testing instructions

To run the tests of Akeneo PIM, please follow:
https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/tests/running_the_tests.md

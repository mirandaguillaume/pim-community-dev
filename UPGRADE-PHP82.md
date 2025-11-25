# PHP 8.2 Upgrade Tracker

- [x] Update `composer.json` PHP constraint to `^8.2` and regenerate `composer.lock` on PHP 8.2 (`composer update --lock` or full update). _(constraint bumped; lock refreshed targeting PHP 8.2 with platform override)_
- [x] Update Docker stack to 8.2: switch image tags in `docker-compose.yml`, install `php8.2-*` packages/paths in `Dockerfile` (fpm/cli, ini, xdebug/blackfire), rebuild images.
- [ ] Run `composer why-not php 8.2` to identify dependency blockers; upgrade or patch as needed.
- [ ] Audit dynamic properties (8.2 deprecation): add typed properties or `#[AllowDynamicProperties]` only when necessary.
- [ ] Run validation on PHP 8.2: `make pim-test`, `vendor/bin/phpspec run`, `make phpstan`, and `bin/console pim:installer:check-requirements`.
  - [x] `bin/console pim:installer:check-requirements` via `docker compose run --rm php ...` (OK on PHP 8.2.29; minor ICU data warning; initial cache/logs perms fixed; DB service required).
  - [ ] `make pim-test`
  - [ ] `vendor/bin/phpspec run`
  - [ ] `make phpstan`
- [x] Run `composer audit` and bump vulnerable deps (e.g. `aws/aws-sdk-php>=3.288.1`, `dompdf/dompdf>=2.0.4`).
- [x] Build Docker PHP 8.2 image locally (`akeneo/pim-php-dev:8.2`).

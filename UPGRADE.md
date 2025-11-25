# UPGRADE FROM 4.0 TO 5.0

The container parameters `mailer_transport`, `mailer_host`, `mailer_port`, `mailer_encryption`, `mailer_user`, `mailer_password`, `mailer_from_address` have been removed. Instead, the env var `MAILER_URL` should be set like `smtp://localhost:25?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com`.

# Upgrade plan: PHP 8.2

- Bump PHP constraint in `composer.json` to `^8.2` and regenerate `composer.lock` on PHP 8.2 (run `composer update --lock` or a full update) to align dependencies.
- Update Docker stack to 8.2: switch image tags in `docker-compose.yml` and install `php8.2-*` packages/paths in `Dockerfile` (fpm/cli, ini paths, xdebug/blackfire), then rebuild the images.
- Run `composer why-not php 8.2` to identify remaining dependency blockers and upgrade/patch them as needed.
- Audit dynamic properties (PHP 8.2 deprecation): add real typed properties or `#[AllowDynamicProperties]` where absolutely necessary.
- After bumps, run validation on PHP 8.2: `make pim-test` (phpunit), `vendor/bin/phpspec run`, `make phpstan`, plus a smoke `bin/console pim:installer:check-requirements`.

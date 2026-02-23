# @deprecated Use Castor instead. See castor.php and castor/ directory.
var/tests/%:
	mkdir -p $@

.PHONY: find-legacy-translations
find-legacy-translations:
	.github/scripts/find_legacy_translations.sh

.PHONY: coupling-back
coupling-back: structure-coupling-back user-management-coupling-back channel-coupling-back enrichment-coupling-back connectivity-connection-coupling-back communication-channel-coupling-back import-export-coupling-back job-coupling-back data-quality-insights-coupling-back enrichment-product-coupling-back migration-coupling-back identifier-generator-coupling-back installer-coupling-back

.PHONY: migration-coupling-back
migration-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=upgrades/.php_cd.php upgrades/schema

### Static tests
static-back: check-pullup check-sf-services enrichment-product-static-back
	echo "Job done! Nothing more to do here..."

.PHONY: check-pullup
check-pullup:
	${PHP_RUN} bin/check-pullup

.PHONY: check-sf-services
check-sf-services:
	$(PHP_RUN) bin/console lint:container

### Lint tests
.PHONY: lint-back
lint-back:
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bin/console cache:warmup
	$(DOCKER_COMPOSE) run --rm php php -d memory_limit=4G vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/phpstan.neon --error-format=github
	${PHP_RUN} vendor/bin/php-cs-fixer fix --dry-run --format=checkstyle --config=.php_cs.php | { command -v cs2pr >/dev/null && cs2pr || cat; }
	$(MAKE) category-lint-back
	$(MAKE) channel-lint-back
	$(MAKE) communication-channel-lint-back
	$(MAKE) connectivity-connection-lint-back
	$(MAKE) data-quality-insights-lint-back
	$(MAKE) data-quality-insights-phpstan
	$(MAKE) enrichment-product-lint-back
	$(MAKE) identifier-generator-lint-back
	$(MAKE) import-export-lint-back
	$(MAKE) installer-lint-back
	$(MAKE) job-lint-back
	$(MAKE) measurement-lint-back
	$(MAKE) migration-lint-back
	# Cache was created with debug enabled, removing it allows a faster one to be created for upcoming tests
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev

.PHONY: deprecation-back
deprecation-back:
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bin/console cache:warmup
	${PHP_RUN} -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1 --error-format=github
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev

.PHONY: migration-lint-back
migration-lint-back:
	$(DOCKER_COMPOSE) run --rm php php vendor/bin/phpstan analyse -c upgrades/phpstan.neon --error-format=github

.PHONY: lint-front
lint-front:
	$(YARN_RUN) lint
	$(MAKE) connectivity-connection-lint-front

### Unit tests
.PHONY: unit-back
unit-back: var/tests/phpspec
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php php vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	.github/scripts/find_non_executed_phpspec.sh
else
	${PHP_RUN} vendor/bin/phpspec run
endif

.PHONY: unit-front
unit-front:
	$(YARN_RUN) unit
	$(MAKE) connectivity-connection-unit-front

### Acceptance tests
.PHONY: acceptance-back
acceptance-back:
	APP_ENV=behat ${PHP_RUN} vendor/bin/behat -p acceptance --format pim --out var/tests/behat --format progress --out std --colors
	$(MAKE) import-export-acceptance-back
	$(MAKE) job-acceptance-back
	$(MAKE) channel-acceptance-back
	$(MAKE) measurement-acceptance-back
	$(MAKE) identifier-generator-acceptance-back
	$(MAKE) installer-acceptance-back

.PHONY: acceptance-front
acceptance-front:
	MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features

### Integration tests
.PHONY: integration-front
integration-front:
	$(YARN_RUN) integration

.PHONY: pim-integration-back
ifeq ($(CI),true)
pim-integration-back: var/tests/phpunit
	.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php PIM_Integration_Test,Akeneo_Connectivity_Connection_Integration,Akeneo_Communication_Channel_Integration,Job_Integration_Test,Channel_Integration_Test,Identifier_Generator_PhpUnit,Installer_Integration_Test
else
pim-integration-back: var/tests/phpunit connectivity-connection-integration-back communication-channel-integration-back job-integration-back channel-integration-back identifier-generator-phpunit-back installer-integration-back
	@echo "Run integration test locally is too long, please use the target defined for your bounded context (ex: bounded-context-integration-back)"
endif

### Migration tests
.PHONY: migration-back
migration-back: var/tests/phpunit
ifeq ($(CI),true)
	.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php PIM_Migration_Test
else
	APP_ENV=test $(PHP_RUN) ./vendor/bin/phpunit -c . --testsuite PIM_Migration_Test
endif

### End to end tests
.PHONY: end-to-end-back
end-to-end-back: var/tests/phpunit
ifeq ($(CI),true)
	.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php End_to_End
else
	@echo "Run end to end test locally is too long, please use the target defined for your bounded context (ex: bounded-context-end-to-end-back)"
endif

end-to-end-front:
	npx playwright test

# How to debug a behat locally?
# -----------------------------
#
# Run the following command:
# make end-to-end-legacy O=my/feature/file.feature:23
#
# Don't forget to pass *O*ption to avoid to run the whole suite.
# Please add dependencies to this target and let it die

.PHONY: end-to-end-legacy
end-to-end-legacy: var/tests/behat
ifeq ($(CI),true)
	.github/scripts/run_behat.sh $(SUITE)
	.github/scripts/run_behat.sh critical
else
	${PHP_RUN} vendor/bin/behat -p legacy -s all ${O}
endif

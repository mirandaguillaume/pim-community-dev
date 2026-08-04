<?php

declare(strict_types=1);

/**
 * auto_prepend_file shim: starts PCOV line coverage for this request and dumps it on shutdown.
 *
 * Installed by docker/php-coverage.d/pcov-on.ini, which is only on the include path when
 * PHP_INI_SCAN_DIR points at that directory — i.e. on a coverage run and never otherwise.
 *
 * WHY NOT A SYMFONY SUBSCRIBER (which is what this replaces): Kernel.php:51-52 loads services from
 * config/services/<APP_ENV>/, so a subscriber registered under config/services/behat/ exists ONLY
 * when APP_ENV=behat. The Playwright suite runs APP_ENV=prod and therefore got no PHP coverage at
 * all. Registering it under config/services/prod/ instead would break real production builds: the
 * Coverage classes are autoload-dev and Symfony validates service classes at container COMPILE
 * time, so a --no-dev install would fail to build. A prepend file sidesteps the container entirely
 * and serves every APP_ENV with one mechanism.
 *
 * Runs BEFORE the framework, so it precedes all error handling: every path is wrapped and silent.
 */

(static function (): void {
    try {
        // HTTP requests are the unit of attribution here: TestMarker holds exactly one test id at a
        // time, and a request's lifetime is short enough that "whatever the marker says right now" is
        // a safe approximation of "who caused this". A CLI process has no such single owner -- Behat
        // itself runs as `vendor/bin/behat` inside this same container, PHP_INI_SCAN_DIR and all, and
        // would otherwise run pcov\start() ONCE at process start and stopAndDump() ONCE at process
        // exit (~2h later), unioning every non-@javascript scenario's kernel-boot + in-process request
        // (Mink's `symfony` session drives $kernel->handle() directly, no HTTP) into a single record
        // stamped with whichever scenario happened to be current at shutdown. Returning here also
        // stops bin/console invocations from writing records.
        if (\PHP_SAPI === 'cli') {
            return;
        }

        if (!\extension_loaded('pcov') || (int) \ini_get('pcov.enabled') !== 1) {
            return;
        }

        $root = \dirname(__DIR__);
        $autoload = $root . '/vendor/autoload.php';

        if (!\is_file($autoload)) {
            return;
        }

        require_once $autoload;

        if (!\class_exists(\Pim\Behat\Coverage\CoverageCollector::class)) {
            return; // a --no-dev install: the Coverage classes are autoload-dev
        }

        $dir = $root . '/var/tests/behat-coverage';
        $collector = \Pim\Behat\Coverage\CoverageCollector::create();
        $collector->start();

        \register_shutdown_function(static function () use ($collector, $dir): void {
            try {
                $collector->stopAndDump($dir);
            } catch (\Throwable) {
                // a coverage dump must never affect the response
            }
        });
    } catch (\Throwable) {
        // never break a request
    }
})();

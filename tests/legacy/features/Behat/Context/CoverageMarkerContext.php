<?php

declare(strict_types=1);

namespace Pim\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Pim\Behat\Coverage\TestMarker;

/**
 * Tells the server-side coverage collector which scenario is running.
 *
 * Inert unless PCOV is collecting, so a normal behat run pays one ini_get() per scenario. The id is
 * `<repo-relative feature path>:<scenario line>`, which is also how behat itself addresses a
 * scenario — so the inventory can be joined back to the suite without a lookup table.
 */
final class CoverageMarkerContext implements Context
{
    public function __construct(private readonly string $dumpDir)
    {
    }

    /** @BeforeScenario */
    public function recordCurrentScenario(BeforeScenarioScope $scope): void
    {
        if (!\extension_loaded('pcov') || (int) \ini_get('pcov.enabled') !== 1) {
            return;
        }

        $file = $scope->getFeature()->getFile() ?? 'unknown.feature';
        $line = $scope->getScenario()->getLine();

        TestMarker::write($this->dumpDir, \sprintf('%s:%d', $this->relative($file), $line));
    }

    private function relative(string $path): string
    {
        // 5, not 4: this file lives at tests/legacy/features/Behat/Context/, and behat.yml's suite
        // `paths:` (e.g. `tests/legacy/features`) are relative to the repo root, not to tests/ --
        // same root as docker/coverage-prepend.php's `dirname(__DIR__)` from docker/.
        $root = \dirname(__DIR__, 5) . '/';

        return \str_starts_with($path, $root) ? \substr($path, \strlen($root)) : $path;
    }
}

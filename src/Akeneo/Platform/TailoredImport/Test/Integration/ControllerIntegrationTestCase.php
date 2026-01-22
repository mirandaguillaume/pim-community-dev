<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Integration;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase as BaseControllerIntegrationTestCase;

/**
 * Minimal stub so phpstan can resolve the base controller integration test case
 * used by Tailored Import tests that are not present in the Community edition.
 */
abstract class ControllerIntegrationTestCase extends BaseControllerIntegrationTestCase
{
}

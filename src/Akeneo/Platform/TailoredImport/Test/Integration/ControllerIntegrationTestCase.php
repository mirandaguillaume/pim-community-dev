<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Integration;

use Akeneo\Platform\Job\Test\Integration\ControllerIntegrationTestCase as BaseControllerIntegrationTestCase;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;

/**
 * Minimal stub so phpstan can resolve the base controller integration test case
 * used by Tailored Import tests that are not present in the Community edition.
 */
abstract class ControllerIntegrationTestCase extends BaseControllerIntegrationTestCase
{
    protected CatalogInterface $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
    }
}

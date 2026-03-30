<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearerRegistryTest extends TestCase
{
    private ClearerRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ClearerRegistry();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyClearer;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyClearerTest extends TestCase
{
    private PropertyClearer $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyClearer();
    }

}

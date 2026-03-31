<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Event\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsEventTest extends TestCase
{
    private ReadProductsEvent $sut;

    protected function setUp(): void
    {
        $this->sut = new ReadProductsEvent(3, 'code');
    }

    public function test_it_provides_the_number_of_read_products(): void
    {
        $this->assertSame(3, $this->sut->getCount());
    }

    public function test_it_returns_a_connection_code(): void
    {
        $this->assertSame('code', $this->sut->getConnectionCode());
    }
}

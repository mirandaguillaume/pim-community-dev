<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceValueTest extends TestCase
{
    private PriceValue $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceValue('100', 'EUR');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PriceValue::class, $this->sut);
    }

    public function test_it_returns_the_amount(): void
    {
        $this->assertSame('100', $this->sut->amount());
    }

    public function test_it_returns_the_currency(): void
    {
        $this->assertSame('EUR', $this->sut->currency());
    }
}

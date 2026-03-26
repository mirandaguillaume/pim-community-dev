<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetTest extends TestCase
{
    private Target $sut;

    protected function setUp(): void
    {
        $this->sut = Target::fromString('sku');
    }

    public function test_it_is_a_target(): void
    {
        $this->assertInstanceOf(Target::class, $this->sut);
    }

    public function test_it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Target::fromString('');
    }

    public function test_it_returns_a_target(): void
    {
        $this->assertSame('sku', $this->sut->asString());
    }
}

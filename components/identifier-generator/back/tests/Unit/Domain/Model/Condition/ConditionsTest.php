<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConditionsTest extends TestCase
{
    private Conditions $sut;

    protected function setUp(): void {}

    public function test_it_cannot_be_instantiated_with_something_else_than_a_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Conditions::fromArray([new \stdClass()]);
    }

    public function test_it_cannot_be_instantiated_with_wrong_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Conditions::fromNormalized([['type' => 'unknown']]);
    }

    public function test_it_cannot_be_instantiated_with_empty_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Conditions::fromNormalized([['value' => true]]);
    }

    public function test_it_can_be_instantiated_with_valid_normalized_value(): void
    {
        Conditions::fromNormalized([['type' => 'enabled', 'value' => true]]);
        $this->addToAssertionCount(1);
    }

    public function test_it_can_be_normalized(): void
    {
        $this->sut = Conditions::fromArray([new Enabled(true)]);
        $this->assertSame([
            ['type' => 'enabled', 'value' => true],
        ], $this->sut->normalize());
    }

    public function test_it_should_return_conjunction(): void
    {
        $condition1 = new Enabled(true);
        $condition2 = Family::fromNormalized(['type' => 'family', 'operator' => 'EMPTY']);
        $this->sut = Conditions::fromArray([$condition1]);
        $this->assertEquals(Conditions::fromArray([$condition1, $condition2]), $this->sut->and([$condition2]));
    }
}

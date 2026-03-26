<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyPropertyTest extends TestCase
{
    private FamilyProperty $sut;

    protected function setUp(): void
    {
        $this->sut = FamilyProperty::fromNormalized([
                'type' => 'family',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3]
            ]);
    }

    public function test_it_is_a_family_generation(): void
    {
        $this->assertInstanceOf(FamilyProperty::class, $this->sut);
    }

    public function test_it_returns_a_type(): void
    {
        $this->assertSame('family', $this->sut->type());
    }

    public function test_it_returns_a_process(): void
    {
        $process = Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 3]);
        $this->assertInstanceOf(Process::class, $this->sut->process());
        $this->assertEquals($process, $this->sut->process());
    }

    public function test_it_normalize_a_family(): void
    {
        $this->assertSame([
                    'type' => 'family',
                    'process' => [
                        'type' => 'truncate',
                        'operator' => '=',
                        'value' => 3
                    ]
                ], $this->sut->normalize());
    }

    public function test_it_should_return_an_implicit_condition(): void
    {
        $this->assertEquals(Family::fromNormalized(['type' => 'family', 'operator' => 'NOT EMPTY']), $this->sut->getImplicitCondition());
    }
}

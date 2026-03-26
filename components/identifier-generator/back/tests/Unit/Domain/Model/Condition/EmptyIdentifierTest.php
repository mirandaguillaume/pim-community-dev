<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyIdentifierTest extends TestCase
{
    private EmptyIdentifier $sut;

    protected function setUp(): void
    {
        $this->sut = new EmptyIdentifier('sku');
    }

    public function test_it_is_a_condition(): void
    {
        $this->assertInstanceOf(EmptyIdentifier::class, $this->sut);
        $this->assertInstanceOf(ConditionInterface::class, $this->sut);
    }
}

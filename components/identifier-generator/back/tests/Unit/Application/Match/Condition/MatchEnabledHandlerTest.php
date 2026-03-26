<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEnabledHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

class MatchEnabledHandlerTest extends TestCase
{
    private MatchEnabledHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new MatchEnabledHandler();
    }

    public function test_it_should_support_only_enabled_conditions(): void
    {
        $this->assertSame(Enabled::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_enabled_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(new EmptyIdentifier('sku'),
                        new ProductProjection(true, null, [], []),);
    }

    public function test_it_matches_only_enabled_products(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
                    Enabled::fromBoolean(true),
                    new ProductProjection(true, '', [], [])
                ));
        $this->assertSame(false, $this->sut->__invoke(
                    Enabled::fromBoolean(true),
                    new ProductProjection(false, '', [], [])
                ));
    }
}

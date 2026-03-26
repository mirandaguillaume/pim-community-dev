<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEmptyIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

class MatchEmptyIdentifierHandlerTest extends TestCase
{
    private MatchEmptyIdentifierHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new MatchEmptyIdentifierHandler();
    }

    public function test_it_should_support_only_empty_identifier_conditions(): void
    {
        $this->assertSame(EmptyIdentifier::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_empty_identifier_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(
            Enabled::fromBoolean(true),
            new ProductProjection(true, null, [], []),
        );
    }

    public function test_it_should_match_product_without_identifier(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [], [])
        ));
    }

    public function test_it_should_match_product_with_empty_identifier(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [
                'sku-<all_channels>-<all_locales>' => '',
            ], [])
        ));
    }

    public function test_it_should_not_match_product_with_filled_identifier(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [
                'sku-<all_channels>-<all_locales>' => 'productidentifier',
            ], [])
        ));
    }
}

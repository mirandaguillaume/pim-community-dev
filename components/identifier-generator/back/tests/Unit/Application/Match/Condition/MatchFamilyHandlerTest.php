<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchFamilyHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

class MatchFamilyHandlerTest extends TestCase
{
    private MatchFamilyHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new MatchFamilyHandler();
    }

    public function test_it_should_support_only_family_conditions(): void
    {
        $this->assertSame(Family::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_family_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(new EmptyIdentifier('sku'),
                        new ProductProjection(true, null, [], []),);
    }

    public function test_it_should_match_empty(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'EMPTY',
                    ]),
                    new ProductProjection(true, null, [], [])
                ));
    }

    public function test_it_should_not_match_empty(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'EMPTY',
                    ]),
                    new ProductProjection(true, 'familyCode', [], [])
                ));
    }

    public function test_it_should_match_not_empty(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'NOT EMPTY',
                    ]),
                    new ProductProjection(true, 'familyCode', [], [])
                ));
    }

    public function test_it_should_not_match_not_empty(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'NOT EMPTY',
                    ]),
                    new ProductProjection(true, null, [], [])
                ));
    }

    public function test_it_should_match_in(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'IN',
                        'value' => ['shirts', 'jeans'],
                    ]),
                    new ProductProjection(true, 'shirts', [], [])
                ));
    }

    public function test_it_should_not_match_in(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'IN',
                        'value' => ['shirts', 'jeans'],
                    ]),
                    new ProductProjection(true, 'jackets', [], [])
                ));
    }

    public function test_it_should_match_not_in(): void
    {
        $this->assertSame(true, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'NOT IN',
                        'value' => ['shirts', 'jeans'],
                    ]),
                    new ProductProjection(true, 'jackets', [], [])
                ));
    }

    public function test_it_should_not_match_not_in(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'NOT IN',
                        'value' => ['shirts', 'jeans'],
                    ]),
                    new ProductProjection(true, 'shirts', [], [])
                ));
    }

    public function test_it_should_not_match_not_in_when_product_has_no_family(): void
    {
        $this->assertSame(false, $this->sut->__invoke(
                    Family::fromNormalized([
                        'type' => 'family',
                        'operator' => 'NOT IN',
                        'value' => ['shirts', 'jeans'],
                    ]),
                    new ProductProjection(true, null, [], [])
                ));
    }
}

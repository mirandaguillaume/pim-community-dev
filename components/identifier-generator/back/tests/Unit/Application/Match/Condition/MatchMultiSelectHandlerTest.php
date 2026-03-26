<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchMultiSelectHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\MultiSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

class MatchMultiSelectHandlerTest extends TestCase
{
    private MatchMultiSelectHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new MatchMultiSelectHandler();
    }

    public function test_it_should_support_only_multi_select_conditions(): void
    {
        $this->assertSame(MultiSelect::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_multi_select_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(new EmptyIdentifier('sku'),
                        new ProductProjection(true, null, [], []),);
    }

    public function test_it_should_match_empty(): void
    {
        $condition = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                ]);
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => 'red',
                ], [])));
    }

    public function test_it_should_match_not_empty(): void
    {
        $condition = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'NOT EMPTY',
                ]);
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => ['red'],
                ], [])));
    }

    public function test_it_should_match_in_list(): void
    {
        $condition = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => ['red', 'pink']
                ]);
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => ['red', 'blue'],
                ], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => ['blue', 'yellow'],
                ], [])));
    }

    public function test_it_should_match_not_in_list(): void
    {
        $condition = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'NOT IN',
                    'value' => ['red', 'pink']
                ]);
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => ['red'],
                ], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'color-<all_channels>-<all_locales>' => ['blue'],
                ], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
    }
}

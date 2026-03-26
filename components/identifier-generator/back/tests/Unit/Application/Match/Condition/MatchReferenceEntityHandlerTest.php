<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchReferenceEntityHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

class MatchReferenceEntityHandlerTest extends TestCase
{
    private MatchReferenceEntityHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new MatchReferenceEntityHandler();
    }

    public function test_it_should_support_only_reference_entity_conditions(): void
    {
        $this->assertSame(ReferenceEntity::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_reference_entity_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(new EmptyIdentifier('sku'),
                        new ProductProjection(true, null, [], []),);
    }

    public function test_it_should_match_not_empty(): void
    {
        $condition = ReferenceEntity::fromNormalized([
                    'type' => 'reference_entity',
                    'attributeCode' => 'brand',
                    'operator' => 'NOT EMPTY',
                ]);
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [
                    'brand-<all_channels>-<all_locales>' => 'akeneo',
                ], [])));
    }
}

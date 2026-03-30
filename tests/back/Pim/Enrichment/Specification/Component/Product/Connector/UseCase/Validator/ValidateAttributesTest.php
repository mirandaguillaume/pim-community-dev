<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAttributes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateAttributesTest extends TestCase
{
    private ValidateAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateAttributes();
    }

    public function test_it_does_not_validate_attributes_if_attributes_are_not_provided(): void
    {
        $queryParametersChecker = $this->createMock(QueryParametersCheckerInterface::class);

        $queryParametersChecker->expects($this->never())->method('checkAttributesParameters');
        $this->sut->validate(null);
    }

    public function test_it_raises_an_exception_if_an_attribute_does_not_exist(): void
    {
        $attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $attributeBuilder = new Builder();
        $color = $attributeBuilder->withCode('color')->build();
        $attributeRepository->method('findOneByIdentifier')->with('color')->willReturn($color);
        $attributeRepository->method('findOneByIdentifier')->with('name')->willReturn(null);
        $this->expectException(new InvalidQueryException('Attribute "name" does not exist.'));
        $this->sut->validate(['color', 'name']);
    }

    public function test_it_raises_an_exception_if_several_attributes_do_not_exist(): void
    {
        $attributeRepository->findOneByIdentifier('color')->willReturn(null);
        $attributeRepository->findOneByIdentifier('name')->willReturn(null);
        $this->expectException(new InvalidQueryException('Attributes "color, name" do not exist.'));
        $this->sut->validate(['color', 'name']);
    }
}

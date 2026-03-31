<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateAttributes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateAttributesTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $attributeRepository;
    private ValidateAttributes $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new ValidateAttributes($this->attributeRepository);
    }

    public function test_it_does_not_validate_attributes_if_attributes_are_not_provided(): void
    {
        $this->attributeRepository->expects($this->never())->method('findOneByIdentifier');
        $this->sut->validate(null);
        $this->addToAssertionCount(1);
    }

    public function test_it_raises_an_exception_if_an_attribute_does_not_exist(): void
    {
        $attributeBuilder = new Builder();
        $color = $attributeBuilder->withCode('color')->build();
        $this->attributeRepository->method('findOneByIdentifier')->willReturnMap([
            ['color', $color],
            ['name', null],
        ]);
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Attribute "name" does not exist.');
        $this->sut->validate(['color', 'name']);
    }

    public function test_it_raises_an_exception_if_several_attributes_do_not_exist(): void
    {
        $this->attributeRepository->method('findOneByIdentifier')->willReturnMap([
            ['color', null],
            ['name', null],
        ]);
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Attributes "color, name" do not exist.');
        $this->sut->validate(['color', 'name']);
    }
}

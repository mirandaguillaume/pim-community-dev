<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\AttributeApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InternalApiRequirementCheckerTest extends TestCase
{
    private FieldsRequirementChecker|MockObject $fieldsChecker;
    private AttributeApiRequirementChecker|MockObject $attributeChecker;
    private InternalApiRequirementChecker $sut;

    protected function setUp(): void
    {
        $this->fieldsChecker = $this->createMock(FieldsRequirementChecker::class);
        $this->attributeChecker = $this->createMock(AttributeApiRequirementChecker::class);
        $this->sut = new InternalApiRequirementChecker($this->fieldsChecker, $this->attributeChecker);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(InternalApiRequirementChecker::class, $this->sut);
        $this->assertInstanceOf(RequirementChecker::class, $this->sut);
    }

    public function testItShouldThrowAnExceptionWhenKeyPropertiesIsMissing(): void
    {
        $data = [
            'attributes' => [],
        ];
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check($data);
    }

    public function testItShouldThrowAnExceptionWhenKeyAttributesIsMissing(): void
    {
        $data = [
            'properties' => [],
        ];
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check($data);
    }

    public function testItShouldCallAllChecker(): void
    {
        $data = [
            'properties' => [],
            'attributes' => [],
        ];
        $this->fieldsChecker->expects($this->once())->method('check')->with($data['properties']);
        $this->attributeChecker->expects($this->once())->method('check')->with($data['attributes']);
        $this->sut->check($data);
    }
}

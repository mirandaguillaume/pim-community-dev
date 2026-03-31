<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\FileGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileGuesserTest extends TestCase
{
    private FileGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new FileGuesser();
    }

    public function test_it_enforces_attribute_type(): void
    {
        $dataProvider = [
            ['pim_catalog_file', true],
            ['pim_catalog_image', true],
            ['pim_catalog_text', false],
        ];

        foreach ($dataProvider as $attributeTypeTest) {
            $attributeType = $attributeTypeTest[0];
            $expectedResult = $attributeTypeTest[1];
            $attribute = $this->createMock(AttributeInterface::class);
            $attribute->method('getType')->willReturn($attributeType);
            $this->assertSame(
                $expectedResult,
                $this->sut->supportAttribute($attribute),
                sprintf('Failed for attribute type "%s"', $attributeType)
            );
        }
    }
}

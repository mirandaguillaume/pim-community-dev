<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ImageValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageValueFactoryTest extends TestCase
{
    private FileInfoRepositoryInterface|MockObject $fileInfoRepository;
    private ImageValueFactory $sut;

    protected function setUp(): void
    {
        $this->fileInfoRepository = $this->createMock(FileInfoRepositoryInterface::class);
        $this->sut = new ImageValueFactory($this->fileInfoRepository);
    }

    public function test_it_is_a_read_value_factory(): void
    {
        $this->assertInstanceOf(ValueFactory::class, $this->sut);
    }

    public function test_it_supports_file_attribute_types(): void
    {
        $this->assertSame(AttributeTypes::IMAGE, $this->sut->supportedAttributeType());
    }

    public function test_it_does_not_support_null(): void
    {
        $this->fileInfoRepository->method('findOneByIdentifier')->with('foo')->willReturn(null);
        $this->expectException(InvalidPropertyException::class);
        $this->sut->createByCheckingData($this->getAttribute(true, true),
                    'ecommerce',
                    'fr_FR',
                    'foo');
    }

    public function test_it_creates_a_localizable_and_scopable_value(): void
    {
        $fileInfo = new FileInfo();
        $this->fileInfoRepository->method('findOneByIdentifier')->with('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, true);
        $value = $this->sut->createByCheckingData($attribute, 'ecommerce', 'fr_FR', 'a_file');
        $this->assertEquals(MediaValue::scopableLocalizableValue('an_attribute', $fileInfo, 'ecommerce', 'fr_FR'), $value);
    }

    public function test_it_creates_a_localizable_value(): void
    {
        $fileInfo = new FileInfo();
        $this->fileInfoRepository->method('findOneByIdentifier')->with('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, false);
        $value = $this->sut->createByCheckingData($attribute, null, 'fr_FR', 'a_file');
        $this->assertEquals(MediaValue::localizableValue('an_attribute', $fileInfo, 'fr_FR'), $value);
    }

    public function test_it_creates_a_scopable_value(): void
    {
        $fileInfo = new FileInfo();
        $this->fileInfoRepository->method('findOneByIdentifier')->with('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, true);
        $value = $this->sut->createByCheckingData($attribute, 'ecommerce', null, 'a_file');
        $this->assertEquals(MediaValue::scopableValue('an_attribute', $fileInfo, 'ecommerce'), $value);
    }

    public function test_it_creates_a_non_localizable_and_non_scopable_value(): void
    {
        $fileInfo = new FileInfo();
        $this->fileInfoRepository->method('findOneByIdentifier')->with('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createByCheckingData($attribute, null, null, 'a_file');
        $this->assertEquals(MediaValue::value('an_attribute', $fileInfo), $value);
    }

    public function test_it_creates_a_value_without_checking_data(): void
    {
        $fileInfo = new FileInfo();
        $this->fileInfoRepository->method('findOneByIdentifier')->with('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, false);
        $value = $this->sut->createWithoutCheckingData($attribute, null, null, 'a_file');
        $this->assertEquals(MediaValue::value('an_attribute', $fileInfo), $value);
    }

    public function test_it_throws_an_exception_if_provided_data_is_not_a_string(): void
    {
        $attribute = $this->getAttribute(false, false);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->createByCheckingData($attribute, null, null, ['an_array']);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
            return new Attribute('an_attribute', AttributeTypes::IMAGE, [], $isLocalizable, $isScopable, null, null, false, 'file', [], $value);
        }
}

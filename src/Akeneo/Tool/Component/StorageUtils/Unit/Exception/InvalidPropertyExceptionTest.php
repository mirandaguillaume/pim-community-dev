<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PHPUnit\Framework\TestCase;

class InvalidPropertyExceptionTest extends TestCase
{
    private InvalidPropertyException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_empty_value_exception(): void
    {
        $exception = InvalidPropertyException::valueNotEmptyExpected(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute'
        );
        $this->sut = new InvalidPropertyException(
            'attribute',
            null,
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" does not expect an empty value.',
            InvalidPropertyException::NOT_EMPTY_VALUE_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_entity_code_exception(): void
    {
        $exception = InvalidPropertyException::validEntityCodeExpected(
            'attribute',
            'code',
            'The attribute does not exist',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'unknown_code'
        );
        $this->sut = new InvalidPropertyException(
            'attribute',
            'unknown_code',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "attribute" expects a valid code. The attribute does not exist, "unknown_code" given.',
            InvalidPropertyException::VALID_ENTITY_CODE_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_date_format_exception(): void
    {
        $exception = InvalidPropertyException::dateExpected(
            'created_date',
            'yyyy-mm-dd',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\DateAttributeSetter',
            '2017/12/12'
        );
        $this->sut = new InvalidPropertyException(
            'created_date',
            '2017/12/12',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\DateAttributeSetter',
            'Property "created_date" expects a string with the format "yyyy-mm-dd" as data, "2017/12/12" given.',
            InvalidPropertyException::DATE_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_date_exception(): void
    {
        $exception = InvalidPropertyException::validDateExpected(
            'created_date',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\DateAttributeSetter',
            '2019-02-31'
        );
        $this->sut = new InvalidPropertyException(
            'created_date',
            '2019-02-31',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\DateAttributeSetter',
            'Property "created_date" expects a valid date as data, "2019-02-31" given.',
            InvalidPropertyException::DATE_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_group_type_exception(): void
    {
        $exception = InvalidPropertyException::validGroupTypeExpected(
            'group',
            'Group is not valid',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'variant'
        );
        $this->sut = new InvalidPropertyException(
            'group',
            'variant',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "group" expects a valid group type. Group is not valid, "variant" given.',
            InvalidPropertyException::VALID_GROUP_TYPE_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_group_exception(): void
    {
        $exception = InvalidPropertyException::validGroupExpected(
            'group',
            'Group is not supported',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'foo'
        );
        $this->sut = new InvalidPropertyException(
            'group',
            'foo',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "group" expects a valid group. Group is not supported, "foo" given.',
            InvalidPropertyException::VALID_GROUP_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_invalid_path_exception(): void
    {
        $exception = InvalidPropertyException::validPathExpected(
            'path',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            '/tmp/foo'
        );
        $this->sut = new InvalidPropertyException(
            'path',
            '/tmp/foo',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "path" expects a valid pathname as data, "/tmp/foo" given.',
            InvalidPropertyException::VALID_PATH_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_an_exception_from_a_previous_exception(): void
    {
        $exception = InvalidPropertyException::expectedFromPreviousException(
            'attribute',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            new \Exception('This is an exception message.', 42)
        );
        $this->sut = new InvalidPropertyException(
            'attribute',
            null,
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'This is an exception message.',
            42
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }

    public function test_it_creates_a_data_expected_exception(): void
    {
        $exception = InvalidPropertyException::dataExpected(
            'name',
            'a valid scope',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter'
        );
        $this->sut = new InvalidPropertyException(
            'name',
            null,
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter',
            'Property "name" expects a valid scope.',
            InvalidPropertyException::VALID_DATA_EXPECTED_CODE
        );
        $this->assertTrue(is_a(InvalidPropertyException::class, $exception::class, true));
        $this->assertSame($exception->getPropertyName(), $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }
}

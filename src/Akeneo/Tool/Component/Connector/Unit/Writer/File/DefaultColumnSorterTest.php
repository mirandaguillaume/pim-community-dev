<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultColumnSorterTest extends TestCase
{
    private FieldSplitter|MockObject $fieldSplitter;
    private DefaultColumnSorter $sut;

    protected function setUp(): void
    {
        $this->fieldSplitter = $this->createMock(FieldSplitter::class);
        $this->sut = new DefaultColumnSorter($this->fieldSplitter, ['code', 'label']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DefaultColumnSorter::class, $this->sut);
    }

    public function test_it_is_a_sorter(): void
    {
        $this->assertInstanceOf(ColumnSorterInterface::class, $this->sut);
    }

    public function test_it_sort_headers_columns(): void
    {
        $qualityScoreField = sprintf('%s-en_US-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX);
        $this->fieldSplitter->method('splitFieldName')->willReturnCallback(function (string $field) use ($qualityScoreField) {
            if ($field === $qualityScoreField) {
                return [GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX];
            }
            return [$field];
        });
        $this->assertSame([
                    'code',
                    'label',
                    'sort_order',
                    $qualityScoreField,
                ], $this->sut->sort([
                    'code',
                    'sort_order',
                    $qualityScoreField,
                    'label',
                ]));
    }
}

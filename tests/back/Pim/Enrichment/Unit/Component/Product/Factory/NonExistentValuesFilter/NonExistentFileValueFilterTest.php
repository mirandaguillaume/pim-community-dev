<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentFileValueFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NonExistentFileValueFilterTest extends TestCase
{
    private FileInfoRepositoryInterface|MockObject $fileInfoRepository;
    private NonExistentFileValueFilter $sut;

    protected function setUp(): void
    {
        $this->fileInfoRepository = $this->createMock(FileInfoRepositoryInterface::class);
        $this->sut = new NonExistentFileValueFilter($this->fileInfoRepository);
    }

    public function test_it_has_a_type(): void
    {
        $this->assertInstanceOf(NonExistentFileValueFilter::class, $this->sut);
    }

    public function test_it_filters_file_and_image_values(): void
    {
        $fileA = $this->createMock(FileInfoInterface::class);
        $imageA = $this->createMock(FileInfoInterface::class);
        $fileB = $this->createMock(FileInfoInterface::class);
        $imageB = $this->createMock(FileInfoInterface::class);

        $ongoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType([
                    AttributeTypes::IMAGE => [
                        'an_image' => [
                            [
                                'identifier' => 'product_A',
                                'values' => ['<all_channels>' => ['<all_locales>' => 'imageA']]
                            ], [
                                'identifier' => 'product_B',
                                'values' => ['ecommerce' => ['en_US' => 'imageB']]
                            ], [
                                'identifier' => 'product_C',
                                'values' => ['<all_channels>' => ['<all_locales>' => 'unexistingImage']]
                            ]
                        ]
                    ],
                    AttributeTypes::FILE => [
                        'a_file' => [
                            [
                                'identifier' => 'product_A',
                                'values' => ['<all_channels>' => ['<all_locales>' => 'fileA']]
                            ], [
                                'identifier' => 'product_B',
                                'values' => ['ecommerce' => ['en_US' => 'fileB']]
                            ], [
                                'identifier' => 'product_C',
                                'values' => ['<all_channels>' => ['<all_locales>' => 'unexistingFile']]
                            ]
                        ]
                    ],
                    AttributeTypes::TEXTAREA => [
                        'a_description' => [
                            [
                                'identifier' => 'product_B',
                                'values' => ['<all_channels>' => ['<all_locales>' => 'plop']]
                            ]
                        ]
                    ]
                ]);
        $fileA->method('getKey')->willReturn('fileA');
        $fileB->method('getKey')->willReturn('fileB');
        $imageA->method('getKey')->willReturn('imageA');
        $imageB->method('getKey')->willReturn('imageB');
        $this->fileInfoRepository->method('findBy')->with(['key' => ['fileA', 'fileB', 'unexistingFile']])->willReturn([$fileA, $fileB]);
        $this->fileInfoRepository->method('findBy')->with(['key' => ['imageA', 'imageB', 'unexistingImage']])->willReturn([$imageA, $imageB]);
        /** @var OnGoingFilteredRawValues $filteredCollection */
                $filteredCollection = $this->sut->filter($ongoingFilteredRawValues);
        $this->assertEquals([
                    AttributeTypes::IMAGE => [
                        'an_image' => [
                            [
                                'identifier' => 'product_A',
                                'values' => ['<all_channels>' => ['<all_locales>' => $imageA]]
                            ], [
                                'identifier' => 'product_B',
                                'values' => ['ecommerce' => ['en_US' => $imageB]]
                            ], [
                                'identifier' => 'product_C',
                                'values' => ['<all_channels>' => ['<all_locales>' => null]]
                            ]
                        ]
                    ],
                    AttributeTypes::FILE => [
                        'a_file' => [
                            [
                                'identifier' => 'product_A',
                                'values' => ['<all_channels>' => ['<all_locales>' => $fileA]]
                            ], [
                                'identifier' => 'product_B',
                                'values' => ['ecommerce' => ['en_US' => $fileB]]
                            ], [
                                'identifier' => 'product_C',
                                'values' => ['<all_channels>' => ['<all_locales>' => null]]
                            ]
                        ]
                    ],
                ], $filteredCollection->filteredRawValuesCollectionIndexedByType());
    }
}

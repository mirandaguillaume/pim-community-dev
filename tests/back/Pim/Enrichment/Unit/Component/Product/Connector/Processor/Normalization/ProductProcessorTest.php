<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedProductQualityScores;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorTest extends TestCase
{
    private ProductProcessor $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductProcessor();
    }

    public function test_it_processes_product_with_filter_on_quality_score(): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $getAttributes = $this->createMock(GetAttributes::class);
        $getNormalizedProductQualityScores = $this->createMock(GetNormalizedProductQualityScores::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $channel = $this->createMock(ChannelInterface::class);
        $locale = $this->createMock(LocaleInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $uuid = Uuid::uuid4();
        $product->method('getUuid')->willReturn($uuid);
        $getAttributes->method('forCode')->with('picture')->willReturn($this->createAttribute('picture'));
        $getAttributes->method('forCode')->with('size')->willReturn($this->createAttribute('size'));
        $attributeRepository->method('findMediaAttributeCodes')->willReturn(['picture']);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('get')->with('filePath')->willReturn('/my/path/product.csv');
        $jobParameters->method('get')->with('filters')->willReturn([
                        'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']],
                        'data' => [['field' => 'quality_score_multi_locales']]
                    ]);
        $jobParameters->method('has')->with('with_media')->willReturn(true);
        $jobParameters->method('get')->with('with_media')->willReturn(false);
        $jobParameters->method('has')->with('with_uuid')->willReturn(true);
        $jobParameters->method('get')->with('with_uuid')->willReturn(false);
        $channelRepository->method('findOneByIdentifier')->with('mobile')->willReturn($channel);
        $channel->method('getLocales')->willReturn(new ArrayCollection([$locale]));
        $channel->method('getCode')->willReturn('foobar');
        $channel->method('getLocaleCodes')->willReturn(['en_US', 'de_DE']);
        $normalizer->method('normalize')->with($product, 'standard', ['with_association_uuids' => false])->willReturn([
                    'enabled'    => true,
                    'categories' => ['cat1', 'cat2'],
                    'values' => [
                        'picture' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'a/b/c/d/e/f/little_cat.jpg'
                            ]
                        ],
                        'size' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'M'
                            ]
                        ]
                    ]
                ]);
        $normalizedProductWithQualityScores = [
                    'enabled'    => true,
                    'categories' => ['cat1', 'cat2'],
                    'values' => [
                        'size' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'M'
                            ]
                        ]
                    ],
                    'quality_scores' => [
                        'mobile' => [
                            'en_US' => 'A',
                            'de_DE' => 'B',
                        ]
                    ]
                ];
        $getNormalizedProductQualityScores->method('__invoke')->with($uuid,'mobile', ['en_US', 'fr_FR'])->willReturn($normalizedProductWithQualityScores['quality_scores']);
        $this->assertEquals($normalizedProductWithQualityScores, $this->sut->process($product));
    }

    private function createAttribute(string $code): Attribute
    {
            return new Attribute(
                $code,
                AttributeTypes::NUMBER,
                [],
                true,
                true,
                null,
                null,
                true,
                'decimal',
                []
            );
        }
}

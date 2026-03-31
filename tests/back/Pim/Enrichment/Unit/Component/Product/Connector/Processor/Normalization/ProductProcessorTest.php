<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProcessorTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private IdentifiableObjectRepositoryInterface|MockObject $channelRepository;
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private FillMissingValuesInterface|MockObject $fillMissingValues;
    private GetAttributes|MockObject $getAttributes;
    private GetNormalizedQualityScoresInterface|MockObject $getNormalizedQualityScores;
    private ProductProcessor $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->fillMissingValues = $this->createMock(FillMissingValuesInterface::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->getNormalizedQualityScores = $this->createMock(GetNormalizedQualityScoresInterface::class);
        $this->sut = new ProductProcessor(
            $this->normalizer,
            $this->channelRepository,
            $this->attributeRepository,
            $this->fillMissingValues,
            $this->getAttributes,
            $this->getNormalizedQualityScores
        );
    }

    public function test_it_processes_product_with_filter_on_quality_score(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $channel = $this->createMock(ChannelInterface::class);
        $locale = $this->createMock(LocaleInterface::class);
        $product = $this->createMock(ProductInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);

        $uuid = Uuid::uuid4();
        $product->method('getUuid')->willReturn($uuid);
        $this->getAttributes->method('forCode')
            ->willReturnCallback(fn (string $code) => $this->createAttribute($code));
        $this->attributeRepository->method('findMediaAttributeCodes')->willReturn(['picture']);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);

        $jobParameters->method('get')
            ->willReturnCallback(fn (string $key) => match ($key) {
                'filePath' => '/my/path/product.csv',
                'filters' => [
                    'structure' => ['scope' => 'mobile', 'locales' => ['en_US', 'fr_FR']],
                    'data' => [['field' => 'quality_score_multi_locales']],
                ],
                'with_media' => false,
                'with_uuid' => false,
                default => null,
            });
        $jobParameters->method('has')
            ->willReturnCallback(fn (string $key) => in_array($key, ['with_media', 'with_uuid']));

        $this->channelRepository->method('findOneByIdentifier')->with('mobile')->willReturn($channel);
        $channel->method('getLocales')->willReturn(new ArrayCollection([$locale]));
        $channel->method('getCode')->willReturn('foobar');
        $channel->method('getLocaleCodes')->willReturn(['en_US', 'de_DE']);

        $this->normalizer->method('normalize')->willReturn([
            'enabled' => true,
            'categories' => ['cat1', 'cat2'],
            'values' => [
                'picture' => [['locale' => null, 'scope' => null, 'data' => 'a/b/c/d/e/f/little_cat.jpg']],
                'size' => [['locale' => null, 'scope' => null, 'data' => 'M']],
            ],
        ]);

        $this->getNormalizedQualityScores->method('__invoke')->willReturn([
            'mobile' => ['en_US' => 'A', 'de_DE' => 'B'],
        ]);

        $expected = [
            'enabled' => true,
            'categories' => ['cat1', 'cat2'],
            'values' => [
                'size' => [['locale' => null, 'scope' => null, 'data' => 'M']],
            ],
            'quality_scores' => [
                'mobile' => ['en_US' => 'A', 'de_DE' => 'B'],
            ],
        ];
        $this->assertEquals($expected, $this->sut->process($product));
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

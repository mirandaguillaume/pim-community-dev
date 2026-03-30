<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdentifier;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\CalculateProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateProductCompletenessTest extends TestCase
{
    private CompletenessCalculator|MockObject $completenessCalculator;
    private CalculateProductCompleteness $sut;

    protected function setUp(): void
    {
        $this->completenessCalculator = $this->createMock(CompletenessCalculator::class);
        $this->sut = new CalculateProductCompleteness($this->completenessCalculator);
    }

    public function test_it_calculate_product_completeness(): void
    {
        $this->assertInstanceOf(CalculateProductCompletenessInterface::class, $this->sut);
    }

    public function test_it_throws_exception_when_product_uuid_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->calculate(new ProductModelId(42), );
    }

    public function test_it_evaluates_the_completeness_criterion(): void
    {
        $uuid = 'df470d52-7723-4890-85a0-e79be625e2ed';
        $productUuid = ProductUuid::fromString($uuid);
        $this->completenessCalculator->method('fromProductUuid')->with(Uuid::fromString($uuid))->willReturn(new ProductCompletenessWithMissingAttributeCodesCollection(
            $uuid,
            [
                        new ProductCompletenessWithMissingAttributeCodes(
                            'ecommerce',
                            'en_US',
                            10,
                            [
                                'name', 'description', 'weight', 'height',
                            ]
                        ),
                        new ProductCompletenessWithMissingAttributeCodes(
                            'ecommerce',
                            'fr_FR',
                            10,
                            [
                                'name', 'description', 'weight', 'height', 'width', 'brand',
                            ]
                        ),
                        new ProductCompletenessWithMissingAttributeCodes(
                            'print',
                            'en_US',
                            12,
                            [
                                'name', 'description', 'weight', 'height',
                            ]
                        ),
                        new ProductCompletenessWithMissingAttributeCodes(
                            'print',
                            'fr_FR',
                            10,
                            [
                                'name', 'description', 'weight', 'height', 'width', 'brand', 'color',
                            ]
                        ),
                    ]
        ));
        $evaluationResult = $this->calculate($productUuid);
        $evaluationResult->getRates()->toArrayInt()->shouldBeLike([
                    'ecommerce' => [
                        'en_US' => 60,
                        'fr_FR' => 40,
                    ],
                    'print' => [
                        'en_US' => 66,
                        'fr_FR' => 30,
                    ],
                ]);
        $evaluationResult->getMissingAttributes()->toArray()->shouldBeLike([
                    'ecommerce' => [
                        'en_US' => ['name', 'description', 'weight', 'height'],
                        'fr_FR' => ['name', 'description', 'weight', 'height', 'width', 'brand'],
                    ],
                    'print' => [
                        'en_US' => ['name', 'description', 'weight', 'height'],
                        'fr_FR' => ['name', 'description', 'weight', 'height', 'width', 'brand', 'color'],
                    ],
                ]);
    }
}

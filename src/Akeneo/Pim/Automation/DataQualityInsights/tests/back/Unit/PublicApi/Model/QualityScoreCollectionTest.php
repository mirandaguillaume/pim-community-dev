<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use PHPUnit\Framework\TestCase;

class QualityScoreCollectionTest extends TestCase
{
    private QualityScoreCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_constructed_and_returns_quality_score(): void
    {
        $expectedQualityScore = new QualityScore('A', 95);
        $qualityScores = [
                    'ecommerce' => [
                        'fr_FR' => $expectedQualityScore,
                        'en_US' => new QualityScore('B', 70),
                    ],
                    'print' => [
                        'fr_FR' => new QualityScore('B', 70),
                        'en_US' => new QualityScore('A', 95),
                    ],
                ];
        $this->sut = new QualityScoreCollection($qualityScores);
        $this->assertSame($expectedQualityScore, $this->sut->getQualityScoreByChannelAndLocale('ecommerce', 'fr_FR'));
        $this->assertNull($this->sut->getQualityScoreByChannelAndLocale('ecommerce', 'es_ES'));
        $this->assertNull($this->sut->getQualityScoreByChannelAndLocale('mobile', 'fr_FR'));
    }
}

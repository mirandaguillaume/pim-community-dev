<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use PHPUnit\Framework\TestCase;

class QualityScoreTest extends TestCase
{
    private QualityScore $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_constructed_and_returns_letter_and_rate(): void
    {
        $letter = 'A';
        $rate = 95;
        $this->sut = new QualityScore($letter, $rate);
        $this->assertSame($letter, $this->sut->getLetter());
        $this->assertSame($rate, $this->sut->getRate());
    }
}

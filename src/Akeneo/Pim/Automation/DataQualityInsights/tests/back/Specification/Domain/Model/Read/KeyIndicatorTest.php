<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use PHPUnit\Framework\TestCase;

class KeyIndicatorTest extends TestCase
{
    private KeyIndicator $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_the_key_indicator_in_array_format(): void
    {
        $this->sut = new KeyIndicator(new KeyIndicatorCode('good_enrichment'), 1, 999, []);
        $this->assertSame([
                    'totalGood' => 1,
                    'totalToImprove' => 999,
                    'extraData' => [],
                ], $this->sut->toArray());
    }

    public function test_it_determines_if_the_key_indicator_is_empty(): void
    {
        $this->sut = new KeyIndicator(new KeyIndicatorCode('good_enrichment'), 0, 0, []);
        $this->assertSame(true, $this->sut->isEmpty());
    }

    public function test_it_determines_if_the_key_indicator_is_not_empty(): void
    {
        $this->sut = new KeyIndicator(new KeyIndicatorCode('good_enrichment'), 1, 0, []);
        $this->assertSame(false, $this->sut->isEmpty());
    }
}

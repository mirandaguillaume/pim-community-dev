<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\Standard\CurrencyNormalizer;
use PHPUnit\Framework\TestCase;

class CurrencyNormalizerTest extends TestCase
{
    private CurrencyNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyNormalizer();
    }

}

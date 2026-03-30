<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\ExternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\ExternalApi\CurrencyNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CurrencyNormalizerTest extends TestCase
{
    private CurrencyNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyNormalizer();
    }

}

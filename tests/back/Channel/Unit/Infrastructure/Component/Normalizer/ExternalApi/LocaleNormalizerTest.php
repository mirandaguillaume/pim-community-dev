<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\ExternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\ExternalApi\LocaleNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizerTest extends TestCase
{
    private LocaleNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleNormalizer();
    }

}

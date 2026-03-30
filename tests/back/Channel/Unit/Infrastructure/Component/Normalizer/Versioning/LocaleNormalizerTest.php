<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\Versioning;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\Standard\LocaleNormalizer as LocaleNormalizerStandard;
use Akeneo\Channel\Infrastructure\Component\Normalizer\Versioning\LocaleNormalizer;
use PHPUnit\Framework\TestCase;

class LocaleNormalizerTest extends TestCase
{
    private LocaleNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleNormalizer();
    }

}

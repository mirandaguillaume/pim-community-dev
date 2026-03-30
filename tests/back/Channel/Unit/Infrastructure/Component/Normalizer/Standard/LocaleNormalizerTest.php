<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\Standard\LocaleNormalizer;
use PHPUnit\Framework\TestCase;

class LocaleNormalizerTest extends TestCase
{
    private LocaleNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleNormalizer();
    }

}

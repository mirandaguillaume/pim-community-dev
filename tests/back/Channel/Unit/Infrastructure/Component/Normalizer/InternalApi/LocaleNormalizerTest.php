<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\InternalApi\LocaleNormalizer;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\TestCase;

class LocaleNormalizerTest extends TestCase
{
    private LocaleNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleNormalizer();
    }

}

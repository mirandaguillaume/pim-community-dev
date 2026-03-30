<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\ExternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\ExternalApi\ChannelNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelNormalizerTest extends TestCase
{
    private ChannelNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}

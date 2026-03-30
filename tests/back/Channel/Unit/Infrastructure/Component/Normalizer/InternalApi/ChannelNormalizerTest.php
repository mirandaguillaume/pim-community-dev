<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Normalizer\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Normalizer\InternalApi\ChannelNormalizer;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelNormalizerTest extends TestCase
{
    private ChannelNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelNormalizer();
    }

}

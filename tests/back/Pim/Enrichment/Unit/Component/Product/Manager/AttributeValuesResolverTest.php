<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Manager;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolver;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class AttributeValuesResolverTest extends TestCase
{
    private AttributeValuesResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeValuesResolver();
    }

}

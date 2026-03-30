<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\ComputeEntityRawValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ComputeEntityRawValuesSubscriberTest extends TestCase
{
    private ComputeEntityRawValuesSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ComputeEntityRawValuesSubscriber();
    }

}

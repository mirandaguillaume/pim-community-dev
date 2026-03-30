<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Normalizer\Event\AttributesWereCreatedOrUpdatedNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributesWereCreatedOrUpdatedNormalizerTest extends TestCase
{
    private AttributesWereCreatedOrUpdatedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributesWereCreatedOrUpdatedNormalizer();
    }

}

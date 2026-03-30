<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\InternalAPI\AttributeGroupNormalizer;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerTest extends TestCase
{
    private AttributeGroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupNormalizer();
    }

}

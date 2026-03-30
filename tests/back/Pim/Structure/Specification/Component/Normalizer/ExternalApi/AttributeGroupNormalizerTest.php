<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeGroupNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerTest extends TestCase
{
    private AttributeGroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}

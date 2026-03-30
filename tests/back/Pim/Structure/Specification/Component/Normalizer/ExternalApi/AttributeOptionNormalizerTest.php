<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeOptionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeOptionNormalizerTest extends TestCase
{
    private AttributeOptionNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}

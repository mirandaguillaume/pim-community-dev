<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\FamilyNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerTest extends TestCase
{
    private FamilyNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}

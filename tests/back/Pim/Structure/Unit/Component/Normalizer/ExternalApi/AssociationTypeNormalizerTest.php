<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AssociationTypeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationTypeNormalizerTest extends TestCase
{
    private AssociationTypeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTypeNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeNormalizer;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAttributeOptionCodes;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerTest extends TestCase
{
    private AttributeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeNormalizer();
    }

}

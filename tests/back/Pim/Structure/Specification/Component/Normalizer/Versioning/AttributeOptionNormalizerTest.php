<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeOptionNormalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AttributeOptionNormalizerTest extends TestCase
{
    private AttributeOptionNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionNormalizer();
    }

}

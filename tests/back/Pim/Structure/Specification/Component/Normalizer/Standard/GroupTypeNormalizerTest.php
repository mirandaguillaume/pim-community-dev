<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\GroupTypeNormalizer;
use PHPUnit\Framework\TestCase;

class GroupTypeNormalizerTest extends TestCase
{
    private GroupTypeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupTypeNormalizer();
    }

}

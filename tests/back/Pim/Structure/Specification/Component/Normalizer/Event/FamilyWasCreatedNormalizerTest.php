<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use Akeneo\Pim\Structure\Component\Normalizer\Event\FamilyWasCreatedNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyWasCreatedNormalizerTest extends TestCase
{
    private FamilyWasCreatedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyWasCreatedNormalizer();
    }

}

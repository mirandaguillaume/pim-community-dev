<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Normalizer\Event\FamilyWasUpdatedNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyWasUpdatedNormalizerTest extends TestCase
{
    private FamilyWasUpdatedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyWasUpdatedNormalizer();
    }

}

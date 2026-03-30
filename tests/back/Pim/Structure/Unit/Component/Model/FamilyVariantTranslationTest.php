<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslation;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslationInterface;
use PHPUnit\Framework\TestCase;

class FamilyVariantTranslationTest extends TestCase
{
    private FamilyVariantTranslation $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantTranslation();
    }

}

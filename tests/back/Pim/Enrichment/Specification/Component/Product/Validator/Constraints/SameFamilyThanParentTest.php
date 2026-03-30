<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class SameFamilyThanParentTest extends TestCase
{
    private SameFamilyThanParent $sut;

    protected function setUp(): void
    {
        $this->sut = new SameFamilyThanParent();
    }

}

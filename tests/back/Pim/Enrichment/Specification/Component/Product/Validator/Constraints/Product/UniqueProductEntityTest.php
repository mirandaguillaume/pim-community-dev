<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class UniqueProductEntityTest extends TestCase
{
    private UniqueProductEntity $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueProductEntity();
    }

}

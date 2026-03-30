<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class UniqueProductModelEntityTest extends TestCase
{
    private UniqueProductModelEntity $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueProductModelEntity();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeConstraintGuesser;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class AttributeConstraintGuesserTest extends TestCase
{
    private AttributeConstraintGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeConstraintGuesser();
    }

}

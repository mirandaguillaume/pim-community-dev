<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\BooleanGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class BooleanGuesserTest extends TestCase
{
    private BooleanGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanGuesser();
    }

}

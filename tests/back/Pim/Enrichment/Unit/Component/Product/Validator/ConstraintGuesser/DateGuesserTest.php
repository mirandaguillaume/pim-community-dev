<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\DateGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Date;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class DateGuesserTest extends TestCase
{
    private DateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new DateGuesser();
    }

}

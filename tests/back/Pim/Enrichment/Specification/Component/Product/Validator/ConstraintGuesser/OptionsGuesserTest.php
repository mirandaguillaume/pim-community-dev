<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\OptionsGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class OptionsGuesserTest extends TestCase
{
    private OptionsGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionsGuesser();
    }

}

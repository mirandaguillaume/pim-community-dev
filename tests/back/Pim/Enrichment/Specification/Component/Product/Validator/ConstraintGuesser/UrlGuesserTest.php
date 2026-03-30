<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UrlGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class UrlGuesserTest extends TestCase
{
    private UrlGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new UrlGuesser();
    }

}

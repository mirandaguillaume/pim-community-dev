<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class IsIdentifierUsableAsGridFilterTest extends TestCase
{
    private IsIdentifierUsableAsGridFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new IsIdentifierUsableAsGridFilter();
    }

}

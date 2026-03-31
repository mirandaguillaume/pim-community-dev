<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Common\Structure\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private Builder $sut;

    protected function setUp(): void
    {
        $this->sut = new Builder();
    }

}

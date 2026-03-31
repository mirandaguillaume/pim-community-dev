<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Akeneo\Test\Acceptance\Attribute\InMemoryIsAttributeEditable;
use PHPUnit\Framework\TestCase;

class InMemoryIsAttributeEditableTest extends TestCase
{
    private InMemoryIsAttributeEditable $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryIsAttributeEditable();
    }

}

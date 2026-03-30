<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\AttributeGroup;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\TestCase;

class AttributeGroupTest extends TestCase
{
    private AttributeGroup $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroup();
    }

}

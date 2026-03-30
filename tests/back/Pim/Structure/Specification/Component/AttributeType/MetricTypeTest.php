<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\MetricType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class MetricTypeTest extends TestCase
{
    private MetricType $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricType();
    }

}

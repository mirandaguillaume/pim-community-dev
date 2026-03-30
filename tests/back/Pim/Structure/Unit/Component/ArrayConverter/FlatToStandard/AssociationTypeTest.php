<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\AssociationType;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\TestCase;

class AssociationTypeTest extends TestCase
{
    private AssociationType $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationType();
    }

}

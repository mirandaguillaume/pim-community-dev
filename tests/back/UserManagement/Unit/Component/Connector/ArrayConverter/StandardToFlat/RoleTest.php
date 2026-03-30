<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    private Role $sut;

    protected function setUp(): void
    {
        $this->sut = new Role();
    }

}

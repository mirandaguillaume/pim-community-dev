<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard\RoleWithPermissions;
use PHPUnit\Framework\TestCase;

class RoleWithPermissionsTest extends TestCase
{
    private RoleWithPermissions $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissions();
    }

}

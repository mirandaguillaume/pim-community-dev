<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $sut;

    protected function setUp(): void
    {
        $this->sut = new User();
    }

}

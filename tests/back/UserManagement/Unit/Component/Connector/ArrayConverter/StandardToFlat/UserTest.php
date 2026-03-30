<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $sut;

    protected function setUp(): void
    {
        $this->sut = new User();
    }

}

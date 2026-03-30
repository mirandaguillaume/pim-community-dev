<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Factory;

use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Factory\SimpleDefaultProperty;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class SimpleDefaultPropertyTest extends TestCase
{
    private SimpleDefaultProperty $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleDefaultProperty();
    }

}

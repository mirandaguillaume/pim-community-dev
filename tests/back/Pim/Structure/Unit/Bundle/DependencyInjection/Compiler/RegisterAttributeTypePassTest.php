<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterAttributeTypePassTest extends TestCase
{
    private RegisterAttributeTypePass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterAttributeTypePass();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterAttributeConstraintGuessersPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterAttributeConstraintGuessersPassTest extends TestCase
{
    private RegisterAttributeConstraintGuessersPass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterAttributeConstraintGuessersPass();
    }

    public function test_it_is_a_compiler_pass(): void
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->sut);
    }
}

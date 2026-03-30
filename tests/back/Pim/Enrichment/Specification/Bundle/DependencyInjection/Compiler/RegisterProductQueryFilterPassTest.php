<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterProductQueryFilterPassTest extends TestCase
{
    private RegisterProductQueryFilterPass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterProductQueryFilterPass();
    }

}

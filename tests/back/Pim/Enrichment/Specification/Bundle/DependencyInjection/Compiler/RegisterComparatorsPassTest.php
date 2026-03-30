<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterComparatorsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterComparatorsPassTest extends TestCase
{
    private RegisterComparatorsPass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterComparatorsPass();
    }

    private function isAnArrayContainingAReferenceAndAPriority($service, $priority)
    {
            return Argument::allOf(
                Argument::withEntry(0, Argument::allOf(
                    Argument::type('Symfony\Component\DependencyInjection\Reference'),
                    Argument::which('__toString', $service)
                ))
            );
        }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterArchiversPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterArchiversPassTest extends TestCase
{
    private RegisterArchiversPass $sut;

    protected function setUp(): void
    {
        $this->sut = new RegisterArchiversPass();
    }

    public function test_it_is_a_compiler_pass(): void
    {
        $this->assertInstanceOf('\\' . \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::class, $this->sut);
    }

    public function test_it_does_not_process_anything_else_than_an_archivist_event_listener(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->method('hasDefinition')->with('pim_connector.event_listener.archivist')->willReturn(false);
        $this->assertNull($this->sut->process($container));
    }

    public function test_it_processes_an_archivist_event_listener_container(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $service = $this->createMock(Definition::class);

        $container->method('hasDefinition')->with('pim_connector.event_listener.archivist')->willReturn(true);
        $container->method('getDefinition')->with('pim_connector.event_listener.archivist')->willReturn($service);
        $container->method('findTaggedServiceIds')->with('pim_connector.archiver')->willReturn([
                    'pim_connector.archiver.invalid_item_csv_archiver' => [[]],
                    'pim_connector.archiver.file_reader_archiver' => [[]],
                ]);
        $service->expects($this->exactly(2))->method('addMethodCall')->with('registerArchiver', $this->isType('array'))->willReturn($service);
        $this->sut->process($container);
    }
}

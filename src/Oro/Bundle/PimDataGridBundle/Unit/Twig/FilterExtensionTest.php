<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Twig\FilterExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;

class FilterExtensionTest extends TestCase
{
    private FiltersConfigurator|MockObject $configurator;
    private TranslatorInterface|MockObject $translator;
    private Manager|MockObject $manager;
    private FilterExtension $sut;

    protected function setUp(): void
    {
        $this->configurator = $this->createMock(FiltersConfigurator::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->manager = $this->createMock(Manager::class);
        $this->sut = new FilterExtension($this->manager, $this->configurator, $this->translator);
        ;
    }

    public function test_it_is_a_twig_extension(): void
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->sut);
    }

    public function test_it_throws_an_exception_when_i_try_to_get_the_label_of_an_unknown_filter(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $configuration = $this->createMock(DatagridConfiguration::class);

        $acceptor->method('getConfig')->willReturn($configuration);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $this->manager->method('getDatagrid')->with('product-grid')->willReturn($datagrid);
        $this->configurator->expects($this->once())->method('configure')->with($configuration);
        $configuration->method('offsetGetByPath')->with('[filters][columns][foo][label]')->willReturn(null);
        $this->assertNull($this->sut->filterLabel('foo'));
    }

    public function test_it_gives_the_label_of_a_filter(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $configuration = $this->createMock(DatagridConfiguration::class);

        $acceptor->method('getConfig')->willReturn($configuration);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $this->manager->method('getDatagrid')->with('product-grid')->willReturn($datagrid);
        $this->configurator->expects($this->once())->method('configure')->with($configuration);
        $configuration->method('offsetGetByPath')->with('[filters][columns][foo][label]')->willReturn('Foo');
        $this->translator->method('trans')->with('Foo')->willReturn('Foo');
        $this->assertSame('Foo', $this->sut->filterLabel('foo'));
    }
}

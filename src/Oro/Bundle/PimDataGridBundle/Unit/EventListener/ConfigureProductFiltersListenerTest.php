<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\EventListener;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\PimDataGridBundle\EventListener\ConfigureProductFiltersListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigureProductFiltersListenerTest extends TestCase
{
    private UserContext|MockObject $context;
    private ConfigureProductFiltersListener $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(UserContext::class);
        $this->sut = new ConfigureProductFiltersListener($this->context);
    }

    public function test_it_does_not_apply_when_user_preference_is_null(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(BuildAfter::class);

        $user->method('getProductGridFilters')->willReturn(null);
        $this->context->method('getUser')->willReturn($user);
        $event->expects($this->never())->method('getDatagrid');
        $this->sut->onBuildAfter($event);
    }

    public function test_it_does_not_apply_when_user_preference_is_empty(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(BuildAfter::class);

        $user->method('getProductGridFilters')->willReturn([]);
        $this->context->method('getUser')->willReturn($user);
        $event->expects($this->never())->method('getDatagrid');
        $this->sut->onBuildAfter($event);
    }

    public function test_it_applies_when_user_preference_is_filled_and_skip_disallowed(): void
    {
        $user = $this->createMock(UserInterface::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $acceptor = $this->createMock(Acceptor::class);
        $config = $this->createMock(DatagridConfiguration::class);
        $event = $this->createMock(BuildAfter::class);

        $config->method('offsetGet')->with('filters')->willReturn(['columns' => [
                    'foo'    => [],
                    'baz'    => [],
                    'scope'  => [],
                    'locale' => [],
                ]]);
        // Track the calls to offsetSetByPath
        $setCalls = [];
        $config->expects($this->exactly(2))->method('offsetSetByPath')->willReturnCallback(
            function (string $path, $value) use (&$setCalls) {
                $setCalls[] = [$path, $value];
            }
        );
        $user->method('getProductGridFilters')->willReturn(['foo', 'bar']);
        $this->context->method('getUser')->willReturn($user);
        $acceptor->method('getConfig')->willReturn($config);
        $datagrid->method('getAcceptor')->willReturn($acceptor);
        $event->method('getDatagrid')->willReturn($datagrid);
        $this->sut->onBuildAfter($event);

        // foo should be enabled, baz should not, scope/locale should be skipped
        $this->assertSame('[filters][columns][foo][enabled]', $setCalls[0][0]);
        $this->assertTrue($setCalls[0][1]);
        $this->assertSame('[filters][columns][baz][enabled]', $setCalls[1][0]);
        $this->assertFalse($setCalls[1][1]);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Installer\Unit\Infrastructure\UserConfigurationResetter;

use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;
use Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserConfigurationResetter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserConfigurationResetterTest extends TestCase
{
    private UserConfigurationResetterInterface|MockObject $userConfigurationResetter1;
    private UserConfigurationResetterInterface|MockObject $userConfigurationResetter2;
    private UserConfigurationResetter $sut;

    protected function setUp(): void
    {
        $this->userConfigurationResetter1 = $this->createMock(UserConfigurationResetterInterface::class);
        $this->userConfigurationResetter2 = $this->createMock(UserConfigurationResetterInterface::class);
        $this->sut = new UserConfigurationResetter([$this->userConfigurationResetter1, $this->userConfigurationResetter2]);
    }

    public function test_it_call_all_user_configuration_resetter(): void
    {
        $this->userConfigurationResetter1->expects($this->once())->method('execute');
        $this->userConfigurationResetter2->expects($this->once())->method('execute');
        $this->sut->execute();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Version;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Version\AuthorProperty;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthorPropertyTest extends TestCase
{
    private TranslatorInterface|MockObject $translator;
    private UserManager|MockObject $userManager;
    private AuthorProperty $sut;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userManager = $this->createMock(UserManager::class);
        $this->sut = new AuthorProperty($this->translator, $this->userManager);
    }

    public function test_it_prepares_an_author_value(): void
    {
        $record = $this->createMock(ResultRecordInterface::class);
        $user = $this->createMock(User::class);

        $record->method('getValue')->with('author')->willReturn('julia');
        $record->method('getValue')->with('context')->willReturn(null);
        $this->userManager->expects($this->once())->method('findUserByUsername')->with($this->anything())->willReturn($user);
        $user->method('getFirstName')->willReturn('Julia');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('julia@zaro.com');
        $this->assertSame('Julia Doe - julia@zaro.com', $this->sut->getValue($record));
    }

    public function test_it_prepares_a_removed_author_value(): void
    {
        $record = $this->createMock(ResultRecordInterface::class);

        $record->method('getValue')->with('author')->willReturn('julia');
        $record->method('getValue')->with('context')->willReturn(null);
        $this->userManager->expects($this->once())->method('findUserByUsername')->with($this->anything())->willReturn(null);
        $this->translator->method('trans')->with('pim_user.user.removed_user')->willReturn('Removed user');
        $this->assertSame(' - Removed user', $this->sut->getValue($record));
    }

    public function test_it_prepares_an_author_value_with_context(): void
    {
        $record = $this->createMock(ResultRecordInterface::class);
        $user = $this->createMock(User::class);

        $record->method('getValue')->with('author')->willReturn('julia');
        $record->method('getValue')->with('context')->willReturn('my context');
        $this->userManager->expects($this->once())->method('findUserByUsername')->with($this->anything())->willReturn($user);
        $user->method('getFirstName')->willReturn('Julia');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('getEmail')->willReturn('julia@zaro.com');
        $this->assertSame('Julia Doe - julia@zaro.com (my context)', $this->sut->getValue($record));
    }
}

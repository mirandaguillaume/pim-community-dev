<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddRemoveVersionSubscriber;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AddRemoveVersionSubscriberTest extends TestCase
{
    private VersionFactory|MockObject $versionFactory;
    private VersionRepositoryInterface|MockObject $versionRepository;
    private TokenStorageInterface|MockObject $tokenStorage;
    private AuthorizationCheckerInterface|MockObject $authorizationChecker;
    private SaverInterface|MockObject $versionSaver;
    private AddRemoveVersionSubscriber $sut;

    protected function setUp(): void
    {
        $this->versionFactory = $this->createMock(VersionFactory::class);
        $this->versionRepository = $this->createMock(VersionRepositoryInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->versionSaver = $this->createMock(SaverInterface::class);
        $this->sut = new AddRemoveVersionSubscriber(
            $this->versionFactory,
            $this->versionRepository,
            $this->tokenStorage,
            $this->authorizationChecker,
            $this->versionSaver
        );
    }

    public function test_it_creates_a_version_on_versionable_object_deletion(): void
    {
        $previousVersion = $this->createMock(VersionInterface::class);
        $removeVersion = $this->createMock(VersionInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $admin = $this->createMock(UserInterface::class);
        $price = $this->createMock(VersionableInterface::class);
        $event = $this->createMock(RemoveEvent::class);

        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($admin);
        $admin->method('getUserIdentifier')->willReturn('admin');
        $this->authorizationChecker->method('isGranted')->with('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->versionRepository->method('getNewestLogEntry')->with($this->anything(), 12, null)->willReturn($previousVersion);
        $previousVersion->method('getVersion')->willReturn(11);
        $previousVersion->method('getSnapshot')->willReturn(['foo' => 'bar']);
        $this->versionFactory->method('create')->with($this->anything(), 12, null, 'admin', 'Deleted')->willReturn($removeVersion);
        $removeVersion->method('setVersion')->with(12)->willReturn($removeVersion);
        $removeVersion->method('setSnapshot')->with(['foo' => 'bar'])->willReturn($removeVersion);
        $removeVersion->method('setChangeset')->with([])->willReturn($removeVersion);
        $saveOptions = ['flush' => true];
        $this->versionSaver->expects($this->once())->method('save')->with($removeVersion, $saveOptions);
        $price->method('getId')->willReturn(12);
        $event->method('getSubject')->willReturn($price);
        $event->method('getSubjectId')->willReturn(12);
        $event->method('getArguments')->willReturn($saveOptions);
        $this->sut->addRemoveVersion($event);
    }

    public function test_it_does_not_create_a_version_on_not_versionable_object_deletion(): void
    {
        $removeVersion = $this->createMock(VersionInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $admin = $this->createMock(UserInterface::class);
        $event = $this->createMock(RemoveEvent::class);

        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($admin);
        $admin->method('getUserIdentifier')->willReturn('admin');
        $this->authorizationChecker->method('isGranted')->with('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->versionSaver->expects($this->never())->method('save')->with($removeVersion, $this->anything());
        $event->method('getSubject')->willReturn($notVersionableObject);
        $this->sut->addRemoveVersion($event);
    }
}

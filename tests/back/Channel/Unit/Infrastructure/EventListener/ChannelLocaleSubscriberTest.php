<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Channel\Infrastructure\EventListener\ChannelLocaleSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ChannelLocaleSubscriberTest extends TestCase
{
    private LocaleRepositoryInterface|MockObject $repository;
    private BulkSaverInterface|MockObject $saver;
    private TokenStorageInterface|MockObject $tokenStorage;
    private JobLauncherInterface|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private TokenInterface|MockObject $token;
    private UserInterface|MockObject $user;
    private ChannelLocaleSubscriber $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LocaleRepositoryInterface::class);
        $this->saver = $this->createMock(BulkSaverInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->jobInstanceRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->sut = new ChannelLocaleSubscriber($this->repository,
            $this->saver,
            $this->tokenStorage,
            $this->jobLauncher,
            $this->jobInstanceRepository,
            'remove_completeness_for_channel_and_locale');
        $this->user->method('getUserIdentifier')->willReturn('julia');
        $this->token->method('getUser')->willReturn($this->user);
        $this->tokenStorage->method('getToken')->willReturn($this->token);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ChannelLocaleSubscriber::class, $this->sut);
    }

    public function test_it_only_handles_channels(): void
    {
        $subject = new \stdClass();
        $this->saver->expects($this->never())->method('saveAll')->with($this->anything());
        $this->sut->storeUpdatedLocales(new GenericEvent($subject));
        $this->sut->saveLocales(new GenericEvent($subject));
    }

    public function test_it_saves_locales_related_to_updated_channels(): void
    {
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $frFR = new Locale();
        $frFR->setCode('fr_FR');
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $channel->addLocale($frFR);
        $this->repository->expects($this->once())->method('getDeletedLocalesForChannel')->with($channel)->willReturn([]);
        $this->saver->expects($this->once())->method('saveAll')->with([$enUS, $frFR]);
        $this->jobLauncher->expects($this->never())->method('launch');
        $this->sut->storeUpdatedLocales(new GenericEvent($channel));
        $this->sut->saveLocales(new GenericEvent($channel));
    }

    public function test_it_saves_locales_removed_from_channels_and_launches_the_clean_completeness_job(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);
        $jobExecution = $this->createMock(JobExecution::class);

        $enUS = new Locale();
        $enUS->setCode('en_US');
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $frFR = new Locale();
        $frFR->setCode('fr_FR');
        $deDE = new Locale();
        $deDE->setCode('de_DE');
        $this->repository->expects($this->once())->method('getDeletedLocalesForChannel')->with($channel)->willReturn([$frFR, $deDE]);
        $this->saver->expects($this->once())->method('saveAll')->with([$enUS, $frFR, $deDE]);
        $this->jobInstanceRepository->expects($this->once())->method('findOneByIdentifier')->with('remove_completeness_for_channel_and_locale')->willReturn($jobInstance);
        $this->jobLauncher->expects($this->once())->method('launch')->with($jobInstance,
                    $this->user,
                    [
                        'locales_identifier' => ['fr_FR', 'de_DE'],
                        'channel_code' => 'ecommerce',
                        'username' => 'julia',
                    ])->willReturn($jobExecution);
        $this->sut->storeUpdatedLocales(new GenericEvent($channel));
        $this->sut->saveLocales(new GenericEvent($channel));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryDataAfterChannelChangeSubscriber;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryDataAfterChannelChangeSubscriberTest extends TestCase
{
    private JobInstanceRepository|MockObject $jobInstanceRepository;
    private JobLauncherInterface|MockObject $jobLauncher;
    private TokenStorageInterface|MockObject $tokenStorage;
    private CleanCategoryDataAfterChannelChangeSubscriber $sut;

    protected function setUp(): void
    {
        $this->jobInstanceRepository = $this->createMock(JobInstanceRepository::class);
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new CleanCategoryDataAfterChannelChangeSubscriber(
            $this->jobInstanceRepository,
            $this->jobLauncher,
            $this->tokenStorage,
        );
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CleanCategoryDataAfterChannelChangeSubscriber::class, $this->sut);
    }

    public function testItPutsInQueueTheJobCleaningCategoryAfterChannelRemoval(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $channel = $this->createMock(Channel::class);
        $cleanCategoriesJobInstance = $this->createMock(JobInstance::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getSubject')->willReturn($channel);
        $channel->method('getCode')->willReturn('deleted_channel_code');
        $channel->method('getLocales')->willReturn(new ArrayCollection([]));
        $this->jobInstanceRepository->method('findOneByIdentifier')->with('clean_categories_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->jobLauncher->expects($this->once())->method('launch')->with(
            $cleanCategoriesJobInstance,
            $user,
            [
                'channel_code' => 'deleted_channel_code',
                'locales_codes' => [],
            ],
        );
        $this->sut->cleanCategoryDataForChannelLocale($event);
    }

    public function testItPutsInQueueTheJobCleaningCategoryAfterChannelUpdate(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $channel = $this->createMock(Channel::class);
        $cleanCategoriesJobInstance = $this->createMock(JobInstance::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);
        $locale = $this->createMock(Locale::class);
        $localesCollection = $this->createMock(ArrayCollection::class);

        $event->method('getSubject')->willReturn($channel);
        $channel->method('getCode')->willReturn('deleted_channel_code');
        $locale->method('getCode')->willReturn('en_US');
        $localesCollection->method('getValues')->willReturn([$locale]);
        $channel->method('getLocales')->willReturn($localesCollection);
        $this->jobInstanceRepository->method('findOneByIdentifier')->with('clean_categories_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->jobLauncher->expects($this->once())->method('launch')->with(
            $cleanCategoriesJobInstance,
            $user,
            [
                'channel_code' => 'deleted_channel_code',
                'locales_codes' => ['en_US'],
            ],
        );
        $this->sut->cleanCategoryDataForChannelLocale($event);
    }

    public function testItDoesNotPutsInQueueTheJobCleaningCategoryIfSubjectIsNotAChannel(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $eventSubject = $this->createMock(Category::class);

        $event->method('getSubject')->willReturn($eventSubject);
        $this->jobInstanceRepository->expects($this->never())->method('findOneByIdentifier')->with('clean_categories_enriched_values');
    }

    public function testItDoesNotPutsInQueueTheJobCleaningCategoryIfFeatureFlagIsDeactivated(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $eventSubject = $this->createMock(Channel::class);

        $event->method('getSubject')->willReturn($eventSubject);
        $this->jobInstanceRepository->expects($this->never())->method('findOneByIdentifier')->with('clean_categories_enriched_values');
    }
}

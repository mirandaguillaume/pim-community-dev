<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\TemplateDeactivatedEvent;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriberTest extends TestCase
{
    private JobInstanceRepository|MockObject $jobInstanceRepository;
    private JobLauncherInterface|MockObject $jobLauncher;
    private TokenStorageInterface|MockObject $tokenStorage;
    private CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber $sut;

    protected function setUp(): void
    {
        $this->jobInstanceRepository = $this->createMock(JobInstanceRepository::class);
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber(
            $this->jobInstanceRepository,
            $this->jobLauncher,
            $this->tokenStorage,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber::class, $this->sut);
    }

    public function test_it_puts_in_queue_the_job_cleaning_category_after_template_deactivation(): void
    {
        $event = $this->createMock(TemplateDeactivatedEvent::class);
        $templateUuid = $this->createMock(TemplateUuid::class);
        $cleanCategoriesJobInstance = $this->createMock(JobInstance::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getTemplateUuid')->willReturn($templateUuid);
        $templateUuid->method('getValue')->willReturn('63b7b051-48bb-4084-a427-20ee32933a8c');
        $this->jobInstanceRepository->method('findOneByIdentifier')->with('clean_category_template_and_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->jobLauncher->expects($this->once())->method('launch')->with(
            $cleanCategoriesJobInstance,
            $user,
            [
                'template_uuid' => '63b7b051-48bb-4084-a427-20ee32933a8c',
            ]
        );
        $this->sut->cleanCategoryDataForTemplate($event);
    }
}

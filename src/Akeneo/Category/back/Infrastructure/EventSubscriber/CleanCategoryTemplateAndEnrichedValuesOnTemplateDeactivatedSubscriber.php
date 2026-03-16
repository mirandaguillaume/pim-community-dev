<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\TemplateDeactivatedEvent;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: TemplateDeactivatedEvent::class, method: 'cleanCategoryDataForTemplate')]
class CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber
{
    public function __construct(
        private readonly JobInstanceRepository $jobInstanceRepository,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function cleanCategoryDataForTemplate(TemplateDeactivatedEvent $event): void
    {
        /** @var JobInstance|null $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('clean_category_template_and_enriched_values');
        if (!$jobInstance instanceof JobInstance) {
            return;
        }

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'template_uuid' => $event->getTemplateUuid()->getValue(),
        ]);
    }
}

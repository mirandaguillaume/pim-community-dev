<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\DispatchFamilyWasCreatedOrUpdatedSubscriber;
use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchFamilyWasCreatedOrUpdatedSubscriberTest extends TestCase
{
    private DispatchFamilyWasCreatedOrUpdatedSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new DispatchFamilyWasCreatedOrUpdatedSubscriber();
    }

    private function setIdOnFamily(Family $family, int $id): void
    {
            $reflectionClass = new \ReflectionClass(Family::class);
            $reflectionClass->getProperty('id')->setValue($family, $id);
        }
}

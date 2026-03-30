<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\DispatchAttributesWereCreatedOrUpdatedSubscriber;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchAttributesWereCreatedOrUpdatedSubscriberTest extends TestCase
{
    private DispatchAttributesWereCreatedOrUpdatedSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new DispatchAttributesWereCreatedOrUpdatedSubscriber();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\TimestampableSubscriber;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class TimestampableSubscriberTest extends TestCase
{
    private TimestampableSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new TimestampableSubscriber();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductRemovedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchProductRemovedEventSubscriberTest extends TestCase
{
    private DispatchProductRemovedEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new DispatchProductRemovedEventSubscriber();
    }

    private function getMessageBus()
    {
            return new class () implements MessageBusInterface
            {
    
                public $messages = [];
    
                public function dispatch($message, array $stamps = []): Envelope
                {
                    $this->messages[] = $message;
    
                    return new Envelope($message);
                }
            };
        }
}

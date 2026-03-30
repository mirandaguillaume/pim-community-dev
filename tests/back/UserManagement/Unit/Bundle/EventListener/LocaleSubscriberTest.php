<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Bundle\EventListener\LocaleSubscriber;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

class LocaleSubscriberTest extends TestCase
{
    private LocaleSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleSubscriber();
    }

}

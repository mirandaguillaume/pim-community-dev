<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventListener;

use Akeneo\Pim\Structure\Bundle\EventListener\RemoveNonExistingProductValuesSubscriber;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RemoveNonExistingProductValuesSubscriberTest extends TestCase
{
    private RemoveNonExistingProductValuesSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveNonExistingProductValuesSubscriber();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Normalizer;

use Akeneo\Category\Infrastructure\Component\Classification\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\UserNormalizer;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizerTest extends TestCase
{
    private UserNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new UserNormalizer();
    }

}

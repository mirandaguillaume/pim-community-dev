<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Normalizer\Standard;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\DateTimeNormalizer;
use Akeneo\UserManagement\Component\Normalizer\Standard\UserNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizerTest extends TestCase
{
    private UserNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new UserNormalizer();
    }

}

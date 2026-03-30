<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi\GroupRepository;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class GroupRepositoryTest extends TestCase
{
    private GroupRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupRepository();
    }

}

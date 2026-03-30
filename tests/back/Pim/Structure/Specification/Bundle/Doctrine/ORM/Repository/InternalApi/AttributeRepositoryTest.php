<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeRepository;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;

class AttributeRepositoryTest extends TestCase
{
    private AttributeRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeRepository();
    }

}

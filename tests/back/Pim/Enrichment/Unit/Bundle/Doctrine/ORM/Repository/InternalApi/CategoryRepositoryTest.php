<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi\CategoryRepository;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\TreeListener;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    private CategoryRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoryRepository();
    }

}

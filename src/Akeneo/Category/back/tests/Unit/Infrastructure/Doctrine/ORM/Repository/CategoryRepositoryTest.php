<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Doctrine\ORM\Repository;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Repository\CategoryRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Gedmo\Tree\Strategy;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\TreeListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    private EntityManager|MockObject $em;
    private ClassMetadata|MockObject $classMetadata;
    private CategoryRepository $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $connection = $this->createMock(Connection::class);
        $statement = $this->createMock(Statement::class);
        $eventManager = $this->createMock(EventManager::class);
        $treeListener = $this->createMock(TreeListener::class);
        $strategy = $this->createMock(Nested::class);
        $property = $this->createMock(\ReflectionProperty::class);

        $connection->method('prepare')->with($this->anything())->willReturn($statement);
        $this->classMetadata->name = 'channel';
        $this->classMetadata->method('getReflectionProperty')->with($this->anything())->willReturn($property);
        $this->em->method('getConnection')->willReturn($connection);
        $this->em->method('getEventManager')->willReturn($eventManager);
        $this->em->method('getClassMetadata')->willReturn($this->classMetadata);
        $strategy->method('getName')->willReturn(Strategy::NESTED);
        $strategy->method('setNodePosition')->willReturn(null);
        $treeListener->method('getStrategy')->willReturn($strategy);
        $configuration = [
            'parent' => 'parent',
            'left'   => 'left',
        ];
        $treeListener->method('getConfiguration')->willReturn($configuration);
        $eventManager->method('getAllListeners')->willReturn([[$treeListener]]);

        $this->sut = new CategoryRepository($this->em, $this->classMetadata);
    }

    public function test_it_is_a_nested_repository(): void
    {
        $this->assertInstanceOf(NestedTreeRepository::class, $this->sut);
    }

    public function test_it_is_a_category_repository(): void
    {
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->sut);
    }

    public function test_it_is_an_identifiable_object_repository(): void
    {
        $this->assertInstanceOf(IdentifiableObjectRepositoryInterface::class, $this->sut);
    }
}

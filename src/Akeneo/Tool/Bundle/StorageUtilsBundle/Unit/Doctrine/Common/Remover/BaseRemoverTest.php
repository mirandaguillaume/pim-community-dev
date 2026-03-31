<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface ModelToRemove
{
    public function getId(): int;
}

class BaseRemoverTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private BaseRemover $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new BaseRemover(
            $this->objectManager,
            $this->eventDispatcher,
            ModelToRemove::class
        );
        $this->eventDispatcher->method('dispatch')->willReturnArgument(0);
    }

    public function test_it_is_a_remover(): void
    {
        $this->assertInstanceOf(RemoverInterface::class, $this->sut);
        $this->assertInstanceOf(BulkRemoverInterface::class, $this->sut);
    }

    public function test_it_removes_the_object_and_flushes_the_unit_of_work(): void
    {
        $type = $this->createMock(ModelToRemove::class);

        $this->objectManager->expects($this->once())->method('remove')->with($type);
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->remove($type);
    }

    public function test_it_removes_the_objects_and_flushes_the_unit_of_work(): void
    {
        $type1 = $this->createMock(ModelToRemove::class);
        $type2 = $this->createMock(ModelToRemove::class);

        $this->objectManager->expects($this->once())->method('remove')->with($type1);
        $this->objectManager->expects($this->once())->method('remove')->with($type2);
        $this->objectManager->expects($this->once())->method('flush');
        $this->sut->removeAll([$type1, $type2]);
    }

    public function test_it_throws_exception_when_remove_anything_else_than_the_expected_class(): void
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                ModelToRemove::class,
                $anythingElse::class
            )
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->remove($anythingElse);
    }
}

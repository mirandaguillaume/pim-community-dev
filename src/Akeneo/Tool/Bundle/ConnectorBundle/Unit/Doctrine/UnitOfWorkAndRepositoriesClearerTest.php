<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\Doctrine;

use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;

class UnitOfWorkAndRepositoriesClearerTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private CachedObjectRepositoryInterface|MockObject $localeRepository;
    private CachedObjectRepositoryInterface|MockObject $currencyRepository;
    private UnitOfWorkAndRepositoriesClearer $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->localeRepository = $this->createMock(CachedObjectRepositoryInterface::class);
        $this->currencyRepository = $this->createMock(CachedObjectRepositoryInterface::class);
        $this->sut = new UnitOfWorkAndRepositoriesClearer($this->entityManager, [$this->localeRepository, $this->currencyRepository]);
    }

    public function test_it_clears_both_uow_and_repositories(): void
    {
        $this->localeRepository->expects($this->once())->method('clear');
        $this->currencyRepository->expects($this->once())->method('clear');
        $this->entityManager->expects($this->once())->method('clear');
        $this->sut->clear();
    }
}

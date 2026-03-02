<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\Doctrine;

use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class UnitOfWorkAndRepositoriesClearerSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager,
        CachedObjectRepositoryInterface $localeRepository,
        CachedObjectRepositoryInterface $currencyRepository
    ) {
        $this->beConstructedWith($entityManager, [$localeRepository, $currencyRepository]);
    }

    public function it_clears_both_uow_and_repositories($entityManager, $localeRepository, $currencyRepository)
    {
        $localeRepository->clear()->shouldBeCalled();
        $currencyRepository->clear()->shouldBeCalled();
        $entityManager->clear()->shouldBeCalled();

        $this->clear();
    }
}

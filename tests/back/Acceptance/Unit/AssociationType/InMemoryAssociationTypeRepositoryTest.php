<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class InMemoryAssociationTypeRepositoryTest extends TestCase
{
    private InMemoryAssociationTypeRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAssociationTypeRepository();
    }

}

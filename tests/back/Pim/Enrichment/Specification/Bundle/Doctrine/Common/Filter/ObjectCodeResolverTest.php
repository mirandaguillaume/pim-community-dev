<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\Common\Filter;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectCodeResolver;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class ObjectCodeResolverTest extends TestCase
{
    private ObjectCodeResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new ObjectCodeResolver();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\QuantifiedAssociationFieldAdder;
use PHPUnit\Framework\TestCase;

class QuantifiedAssociationFieldAdderTest extends TestCase
{
    private QuantifiedAssociationFieldAdder $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedAssociationFieldAdder();
    }

}

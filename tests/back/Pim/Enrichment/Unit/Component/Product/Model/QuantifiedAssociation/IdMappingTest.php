<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdMappingTest extends TestCase
{
    private IdMapping $sut;

    protected function setUp(): void
    {
        $this->sut = IdMapping::createFromMapping([1 => 'product_identifier']);
    }

    public function test_it_is_created_from_a_mapping_and_returns_the_id_or_the_identifier(): void
    {
        $id = 1;
        $identifier = 'product_identifier';
        $this->sut = IdMapping::createFromMapping([$id => $identifier]);
        $this->assertSame($identifier, $this->sut->getIdentifier($id));
        $this->assertSame($id, $this->sut->getId($identifier));
        $this->assertSame(true, $this->sut->hasId($identifier));
        $this->assertSame(true, $this->sut->hasIdentifier($id));
        $this->assertSame(false, $this->sut->hasId('nice'));
        $this->assertSame(false, $this->sut->hasIdentifier(12));
    }

    public function test_it_throws_if_the_product_id_is_not_an_integer(): void
    {
        $invalidId = 'wrong_id';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createFromMapping([$invalidId => 'product_identifier']);
    }

    public function test_it_throws_if_the_identifier_is_not_an_non_empty_string(): void
    {
        $invalidIdentifier = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createFromMapping([1 => $invalidIdentifier]);
    }
}

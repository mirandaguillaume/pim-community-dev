<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\FamilyUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUserIntentFactoryTest extends TestCase
{
    private FamilyUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyUserIntentFactory();
    }

    public function test_it_returns_a_set_family(): void
    {
        $this->assertEquals([new SetFamily('accessories')], $this->sut->create('family', 'accessories'));
    }

    public function test_it_returns_a_remove_family(): void
    {
        $this->assertEquals([new RemoveFamily()], $this->sut->create('family', null));
        $this->assertEquals([new RemoveFamily()], $this->sut->create('family', ''));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('family', 12);
    }
}

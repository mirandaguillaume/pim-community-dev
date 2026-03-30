<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\EnabledUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class EnabledUserIntentFactoryTest extends TestCase
{
    private EnabledUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new EnabledUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EnabledUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_a_set_enabled_user_intent(): void
    {
        $this->assertEquals([new SetEnabled(true)], $this->sut->create('enabled', true));
    }

    public function test_it_throws_an_error_when_data_is_invalid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('enabled', 10);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('enabled', null);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('enabled', 'toto');
    }
}

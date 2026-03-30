<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FlowTypeTest extends TestCase
{
    private FlowType $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new FlowType(FlowType::OTHER);
        $this->assertTrue(is_a(FlowType::class, FlowType::class, true));
    }

    public function test_it_cannot_be_created_with_an_unknown_flow_type(): void
    {
        $this->expectException(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.flow_type.invalid'));
        new FlowType('foo');
    }

    public function test_it_creates_a_data_destination_flow_type(): void
    {
        $this->sut = new FlowType(FlowType::DATA_DESTINATION);
        $this->assertTrue(is_a(FlowType::class, FlowType::class, true));
    }

    public function test_it_creates_a_data_source_flow_type(): void
    {
        $this->sut = new FlowType(FlowType::DATA_SOURCE);
        $this->assertTrue(is_a(FlowType::class, FlowType::class, true));
    }

    public function test_it_creates_an_others_flow_type(): void
    {
        $this->sut = new FlowType(FlowType::OTHER);
        $this->assertTrue(is_a(FlowType::class, FlowType::class, true));
    }

    public function test_it_returns_the_flow_type_as_string(): void
    {
        $this->sut = new FlowType(FlowType::DATA_SOURCE);
        $this->assertSame(FlowType::DATA_SOURCE, $this->sut->__toString());
    }
}

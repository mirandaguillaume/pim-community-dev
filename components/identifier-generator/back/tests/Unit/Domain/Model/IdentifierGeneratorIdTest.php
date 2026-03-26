<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorIdTest extends TestCase
{
    private IdentifierGeneratorId $sut;

    protected function setUp(): void
    {
        $this->sut = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
    }

    public function test_it_is_an_identifier_generator_id(): void
    {
        $this->assertInstanceOf(IdentifierGeneratorId::class, $this->sut);
    }

    public function test_it_returns_an_identifier_generator_id(): void
    {
        $this->assertSame('2038e1c9-68ff-4833-b06f-01e42d206002', $this->sut->asString());
    }

    public function test_it_cannot_be_instantiated_with_not_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IdentifierGeneratorId::fromString('not_uuid');
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextTransformationTest extends TestCase
{
    private TextTransformation $sut;

    protected function setUp(): void
    {
        $this->sut = TextTransformation::fromString('no');
    }

    public function test_it_is_a_target(): void
    {
        $this->assertInstanceOf(TextTransformation::class, $this->sut);
    }

    public function test_it_cannot_be_instantiated_with_an_unknown_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TextTransformation::fromString('unknown');
    }

    public function test_it_returns_a_text_transformation(): void
    {
        $this->assertSame('no', $this->sut->normalize());
    }
}

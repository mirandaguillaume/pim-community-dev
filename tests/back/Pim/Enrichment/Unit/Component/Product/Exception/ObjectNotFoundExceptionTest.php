<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectNotFoundExceptionTest extends TestCase
{
    public function test_it_is_a_logic_exception(): void
    {
        $this->assertInstanceOf(\LogicException::class, new ObjectNotFoundException());
    }

    public function test_it_defaults_to_a_generic_message(): void
    {
        $this->assertSame('Object was not found.', new ObjectNotFoundException()->getMessage());
    }

    public function test_it_accepts_a_custom_message_code_and_previous_exception(): void
    {
        $previous = new \RuntimeException('the cause');

        $exception = new ObjectNotFoundException('a_product could not be found.', 404, $previous);

        $this->assertSame('a_product could not be found.', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}

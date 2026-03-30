<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDateAttributeException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidDateAttributeExceptionTest extends TestCase
{
    private InvalidDateAttributeException $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidDateAttributeException();
    }

}

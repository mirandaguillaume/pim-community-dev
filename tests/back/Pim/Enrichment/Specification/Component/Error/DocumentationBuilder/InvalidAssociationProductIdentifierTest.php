<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\InvalidAssociationProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAssociationProductIdentifierException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidAssociationProductIdentifierTest extends TestCase
{
    private InvalidAssociationProductIdentifier $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidAssociationProductIdentifier();
    }

}

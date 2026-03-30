<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DocumentationBuilderRegistryTest extends TestCase
{
    private DocumentationBuilderRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new DocumentationBuilderRegistry();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\NotEmptyFamily;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily as ConstraintNotEmptyFamily;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamilyTest extends TestCase
{
    private NotEmptyFamily $sut;

    protected function setUp(): void
    {
        $this->sut = new NotEmptyFamily();
    }

}

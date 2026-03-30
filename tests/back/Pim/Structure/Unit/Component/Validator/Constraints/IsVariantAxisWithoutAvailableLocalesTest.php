<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocales;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesTest extends TestCase
{
    private IsVariantAxisWithoutAvailableLocales $sut;

    protected function setUp(): void
    {
        $this->sut = new IsVariantAxisWithoutAvailableLocales();
    }

}

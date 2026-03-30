<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Email as BaseEmail;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailTest extends TestCase
{
    private Email $sut;

    protected function setUp(): void
    {
        $this->sut = new Email();
    }

}

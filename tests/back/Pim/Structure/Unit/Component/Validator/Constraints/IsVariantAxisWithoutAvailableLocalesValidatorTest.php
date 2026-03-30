<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\AttributeIsAFamilyVariantAxis;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocales;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocalesValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesValidatorTest extends TestCase
{
    private IsVariantAxisWithoutAvailableLocalesValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsVariantAxisWithoutAvailableLocalesValidator();
    }

}

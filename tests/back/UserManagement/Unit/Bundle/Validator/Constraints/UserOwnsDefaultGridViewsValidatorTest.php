<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\UserOwnsDefaultGridViews;
use Akeneo\UserManagement\Bundle\Validator\Constraints\UserOwnsDefaultGridViewsValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserOwnsDefaultGridViewsValidatorTest extends TestCase
{
    private UserOwnsDefaultGridViewsValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new UserOwnsDefaultGridViewsValidator();
    }

}

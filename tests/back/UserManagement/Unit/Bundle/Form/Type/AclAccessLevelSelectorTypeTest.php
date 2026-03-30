<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Form\Type;

use Akeneo\UserManagement\Bundle\Form\Transformer\AccessLevelToBooleanTransformer;
use Akeneo\UserManagement\Bundle\Form\Type\AclAccessLevelSelectorType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclAccessLevelSelectorTypeTest extends TestCase
{
    private AclAccessLevelSelectorType $sut;

    protected function setUp(): void
    {
        $this->sut = new AclAccessLevelSelectorType();
    }

}

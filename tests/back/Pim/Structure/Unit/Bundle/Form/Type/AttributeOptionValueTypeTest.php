<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Form\Type;

use Akeneo\Pim\Structure\Bundle\Form\Type\AttributeOptionValueType;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionValueTypeTest extends TestCase
{
    private AttributeOptionValueType $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionValueType();
    }

}

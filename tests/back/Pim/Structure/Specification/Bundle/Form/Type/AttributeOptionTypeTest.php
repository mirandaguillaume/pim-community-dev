<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Form\Type;

use Akeneo\Pim\Structure\Bundle\Form\Type\AttributeOptionType;
use Akeneo\Pim\Structure\Bundle\Form\Type\AttributeOptionValueType;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionTypeTest extends TestCase
{
    private AttributeOptionType $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionType();
    }

}

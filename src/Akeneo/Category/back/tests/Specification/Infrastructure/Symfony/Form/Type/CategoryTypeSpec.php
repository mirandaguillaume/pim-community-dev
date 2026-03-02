<?php

namespace Specification\Akeneo\Category\Infrastructure\Symfony\Form\Type;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Akeneo\Category\Infrastructure\Symfony\Form\Type\CategoryType;
use Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryTypeSpec extends ObjectBehavior
{
    public function let(FormBuilderInterface $builder)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->beConstructedWith(
            Category::class,
            CategoryTranslation::class
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CategoryType::class);
    }

    public function it_is_a_form_type()
    {
        $this->shouldHaveType(AbstractType::class);
    }

    public function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_category');
    }

    public function it_builds_the_category_form($builder)
    {
        $builder->add('code')->shouldBeCalled();
        $builder->add(
            'label',
            TranslatableFieldType::class,
            Argument::type('array')
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    public function it_adds_a_disable_field_subscriber($builder)
    {
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'))
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    public function it_sets_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => Category::class,
            ]
        )->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    public function it_adds_registered_event_subscribers($builder, EventSubscriberInterface $subscriber)
    {
        $this->addEventSubscriber($subscriber);
        $builder->addEventSubscriber($subscriber)
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }
}

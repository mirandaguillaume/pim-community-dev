<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Symfony\Form\Type;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Akeneo\Category\Infrastructure\Symfony\Form\Type\CategoryType;
use Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryTypeTest extends TestCase
{
    private FormBuilderInterface|MockObject $builder;
    private CategoryType $sut;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(FormBuilderInterface::class);
        $this->sut = new CategoryType(
            Category::class,
            CategoryTranslation::class,
        );
        $this->builder->method('add')->willReturn($this->builder);
        $this->builder->method('addEventSubscriber')->with($this->anything())->willReturn($this->builder);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryType::class, $this->sut);
    }

    public function testItIsAFormType(): void
    {
        $this->assertInstanceOf(AbstractType::class, $this->sut);
    }

    public function testItHasABlockPrefix(): void
    {
        $this->assertSame('pim_category', $this->sut->getBlockPrefix());
    }

    public function testItBuildsTheCategoryForm(): void
    {
        $addedFields = [];
        $this->builder->expects($this->atLeast(2))->method('add')->willReturnCallback(
            function (string $name) use (&$addedFields) {
                $addedFields[] = $name;

                return $this->builder;
            },
        );
        $this->sut->buildForm($this->builder, []);
        $this->assertContains('code', $addedFields);
        $this->assertContains('label', $addedFields);
    }

    public function testItAddsADisableFieldSubscriber(): void
    {
        $this->builder->expects($this->atLeastOnce())->method('addEventSubscriber')->with($this->callback(
            fn ($subscriber) => $subscriber instanceof DisableFieldSubscriber || $subscriber instanceof EventSubscriberInterface,
        ))->willReturn($this->builder);
        $this->sut->buildForm($this->builder, []);
    }

    public function testItSetsDefaultOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver->expects($this->once())->method('setDefaults')->with([
            'data_class' => Category::class,
        ])->willReturn($resolver);
        $this->sut->configureOptions($resolver);
    }

    public function testItAddsRegisteredEventSubscribers(): void
    {
        $subscriber = $this->createMock(EventSubscriberInterface::class);

        $this->sut->addEventSubscriber($subscriber);
        $this->builder->expects($this->atLeastOnce())->method('addEventSubscriber')->willReturn($this->builder);
        $this->sut->buildForm($this->builder, []);
    }

    public function testItAddsEachSubscriberFromIteration(): void
    {
        $sub1 = $this->createMock(EventSubscriberInterface::class);
        $sub2 = $this->createMock(EventSubscriberInterface::class);

        $this->sut->addEventSubscriber($sub1);
        $this->sut->addEventSubscriber($sub2);

        $addedSubscribers = [];
        $this->builder->expects($this->atLeast(3))
            ->method('addEventSubscriber')
            ->willReturnCallback(function ($subscriber) use (&$addedSubscribers) {
                $addedSubscribers[] = $subscriber;

                return $this->builder;
            });

        $this->sut->buildForm($this->builder, []);

        // Should include DisableFieldSubscriber + both custom subscribers
        $this->assertCount(3, $addedSubscribers);
        $this->assertInstanceOf(DisableFieldSubscriber::class, $addedSubscribers[0]);
        $this->assertSame($sub1, $addedSubscribers[1]);
        $this->assertSame($sub2, $addedSubscribers[2]);
    }

    public function testBuildFormAddsLabelFieldWithTranslatableType(): void
    {
        $addCalls = [];
        $this->builder->expects($this->atLeast(2))->method('add')->willReturnCallback(
            function (string $name, ?string $type = null, array $options = []) use (&$addCalls) {
                $addCalls[] = ['name' => $name, 'type' => $type, 'options' => $options];

                return $this->builder;
            },
        );
        $this->sut->buildForm($this->builder, []);

        // Find the label field call
        $labelCall = null;
        foreach ($addCalls as $call) {
            if ($call['name'] === 'label') {
                $labelCall = $call;
                break;
            }
        }
        $this->assertNotNull($labelCall, 'Label field should be added');
        $this->assertSame(TranslatableFieldType::class, $labelCall['type']);
        $this->assertArrayHasKey('field', $labelCall['options']);
        $this->assertSame('label', $labelCall['options']['field']);
        $this->assertArrayHasKey('translation_class', $labelCall['options']);
        $this->assertSame(CategoryTranslation::class, $labelCall['options']['translation_class']);
        $this->assertArrayHasKey('entity_class', $labelCall['options']);
        $this->assertSame(Category::class, $labelCall['options']['entity_class']);
        $this->assertArrayHasKey('property_path', $labelCall['options']);
        $this->assertSame('translations', $labelCall['options']['property_path']);
    }

    public function testBuildFormWithNoSubscribers(): void
    {
        // Create fresh instance with no subscribers
        $sut = new CategoryType(Category::class, CategoryTranslation::class);
        $addedSubscribers = [];
        $this->builder->expects($this->once())
            ->method('addEventSubscriber')
            ->willReturnCallback(function ($subscriber) use (&$addedSubscribers) {
                $addedSubscribers[] = $subscriber;

                return $this->builder;
            });
        $sut->buildForm($this->builder, []);
        // Only the DisableFieldSubscriber should be added
        $this->assertCount(1, $addedSubscribers);
        $this->assertInstanceOf(DisableFieldSubscriber::class, $addedSubscribers[0]);
    }
}

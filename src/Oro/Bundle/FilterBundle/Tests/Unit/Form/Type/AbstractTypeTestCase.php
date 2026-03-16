<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTypeTestCase extends FormIntegrationTestCase
{
    protected ?string $defaultLocale = null;

    protected ?string $defaultTimezone = null;

    private ?string $oldLocale = null;

    private ?string $oldTimezone = null;

    /**
     * @var FormExtensionInterface[]
     */
    protected $formExtensions = [];

    protected function setUp(): void
    {
        parent::setUp();
        if ($this->defaultLocale) {
            $this->oldLocale = \Locale::getDefault();
            \Locale::setDefault($this->defaultLocale);
        }
        if ($this->defaultTimezone) {
            $this->oldTimezone = date_default_timezone_get();
            date_default_timezone_set($this->defaultTimezone);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->defaultLocale) {
            \Locale::setDefault($this->oldLocale);
        }
        if ($this->defaultTimezone) {
            date_default_timezone_set($this->oldTimezone);
        }
    }

    protected function createMockTranslator(): TranslatorInterface
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->anything(), [])
            ->willReturnArgument(0);

        return $translator;
    }

    protected function createMockOptionsResolver(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(\Symfony\Component\OptionsResolver\OptionsResolver::class);
    }

    /**
     * @dataProvider configureOptionsDataProvider
     */
    public function testSetDefaultOptions(array $defaultOptions, array $requiredOptions = [])
    {
        $resolver = $this->createMockOptionsResolver();

        if ($defaultOptions) {
            $resolver->expects($this->once())->method('setDefaults')->with($defaultOptions)->willReturnSelf();
        }

        if ($requiredOptions) {
            $resolver->expects($this->once())->method('setRequired')->with($requiredOptions)->willReturnSelf();
        }

        $this->getTestFormType()->configureOptions($resolver);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public static function configureOptionsDataProvider();

    /**
     * @dataProvider bindDataProvider
     */
    public function testBindData(
        array $bindData,
        array $formData,
        array $viewData,
        array $customOptions = []
    ) {
        $form = $this->factory->create($this->getTestFormType(), null, $customOptions);

        $form->submit($bindData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();

        foreach ($viewData as $key => $value) {
            $this->assertArrayHasKey($key, $view->vars);
            $this->assertEquals($value, $view->vars[$key]);
        }
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public static function bindDataProvider();

    /**
     * @return FormTypeInterface
     */
    abstract protected function getTestFormType();

    /**
     * @return array|FormExtensionInterface[]
     */
    protected function getExtensions()
    {
        return $this->formExtensions;
    }
}

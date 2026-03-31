<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use PHPUnit\Framework\TestCase;

class LocalizableAndScopableAttributeExceptionTest extends TestCase
{
    private LocalizableAndScopableAttributeException $sut;

    protected function setUp(): void
    {
        $this->sut = LocalizableAndScopableAttributeException::fromAttributeChannelAndLocale('description', 'ecommerce', 'en_US');
    }

    public function test_it_is_a_domain_and_templated_error_exception(): void
    {
        $this->assertInstanceOf(LocalizableAndScopableAttributeException::class, $this->sut);
        $this->assertInstanceOf(DomainErrorInterface::class, $this->sut);
        $this->assertInstanceOf(TemplatedErrorMessageInterface::class, $this->sut);
    }

    public function test_it_provides_a_templated_error_message(): void
    {
        $templatedMessage = $this->sut->getTemplatedErrorMessage();
        $this->assertSame(
            'The {attribute_code} attribute requires a value per channel ({channel_code} was detected)' .
            ' and a locale ({locale_code} was detected).',
            $templatedMessage->getTemplate()
        );
        $this->assertSame([
                    'attribute_code' => 'description',
                    'channel_code' => 'ecommerce',
                    'locale_code' => 'en_US',
                ], $templatedMessage->getParameters());
    }

    public function test_it_provides_the_attribute_code(): void
    {
        $this->assertSame('description', $this->sut->getAttributeCode());
    }

    public function test_it_provides_the_property_name(): void
    {
        $this->assertSame('attribute', $this->sut->getPropertyName());
    }

    public function test_it_provides_a_message_with_null_parameters(): void
    {
        $this->sut = LocalizableAndScopableAttributeException::fromAttributeChannelAndLocale('description', null, null);
        $this->assertSame('The description attribute requires a value per channel (nothing was detected)' .
                    ' and a locale (nothing was detected).', $this->sut->getMessage());
    }
}

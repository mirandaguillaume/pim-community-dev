<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use PHPUnit\Framework\TestCase;

class BusinessErrorTest extends TestCase
{
    private BusinessError $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_business_error(): void
    {
        $this->sut = new BusinessError($this->getWellStructuredContent());
        $this->assertTrue(is_a(BusinessError::class, BusinessError::class, true));
        $this->assertTrue(is_a(BusinessError::class, ApiErrorInterface::class, true));
    }

    public function test_it_provides_a_content(): void
    {
        $this->sut = new BusinessError($this->getWellStructuredContent());
        $this->assertSame($this->getWellStructuredContent(), $this->sut->content());
    }

    public function test_it_must_have_a_json_content(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BusinessError('');
    }

    public function test_it_must_have_a_content(): void
    {
        $this->expectException(new \InvalidArgumentException(
            'The API error must have a content, but you provided en empty json.'
        ));
        new BusinessError('{}');
    }

    public function test_it_normalizes(): void
    {
        $dateTime = new \DateTimeImmutable('2020-01-01T00:00:00', new \DateTimeZone('UTC'));
        $content = $this->getWellStructuredContent();
        $this->sut = new BusinessError($content, $dateTime);
        $expected = [
                    'id' => $this->id(),
                    'content' => \json_decode($content, true, 512, JSON_THROW_ON_ERROR),
                    'error_datetime' => '2020-01-01T00:00:00+00:00',
                ];
        $this->assertSame($expected, $this->sut->normalize());
    }

    public function test_it_provides_an_error_type(): void
    {
        $this->sut = new BusinessError($this->getWellStructuredContent());
        $type = $this->type();
        $type->shouldBeAnInstanceOf(ErrorType::class);
        $type->__toString()->shouldReturn(ErrorTypes::BUSINESS);
    }

    private function getWellStructuredContent(): string
    {
        return <<<JSON
                {
                    "code": 422,
                    "_links": {
                        "documentation": {
                            "href": "http://api.akeneo.com/api-reference.html#post_products"
                        }
                    },
                    "message": "Property \"description\" does not exist. Check the expected format on the API documentation."
                }
                JSON;
    }
}

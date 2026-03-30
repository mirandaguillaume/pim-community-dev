<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\CollectApiError;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\EventSubscriber\ApiErrorEventSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use FOS\RestBundle\Context\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;

class ApiErrorEventSubscriberTest extends TestCase
{
    private CollectApiError|MockObject $collectApiError;
    private ApiErrorEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->collectApiError = $this->createMock(CollectApiError::class);
        $this->sut = new ApiErrorEventSubscriber($this->collectApiError);
    }

    public function test_it_is_an_event_subscriber(): void
    {
        $this->assertInstanceOf(ApiErrorEventSubscriber::class, $this->sut);
    }

    public function test_it_collects_a_product_domain_error(): void
    {
        $error = new UnknownAttributeException('attribute_code');
        $product = new Product();
        $event = new ProductDomainErrorEvent($error, $product);
        $context = (new Context())->setAttribute('product', $event->getProduct());
        $this->collectApiError->expects($this->once())->method('collectFromProductDomainError')->with($error, $context);
        $this->sut->collectProductDomainError($event);
    }

    public function test_it_collects_a_product_validation_error(): void
    {
        $constraintViolationList = new ConstraintViolationList();
        $product = new Product();
        $event = new ProductValidationErrorEvent($constraintViolationList, $product);
        $context = (new Context())->setAttribute('product', $event->getProduct());
        $this->collectApiError->expects($this->once())->method('collectFromProductValidationError')->with($constraintViolationList, $context);
        $this->sut->collectProductValidationError($event);
    }

    public function test_it_collects_a_technical_error(): void
    {
        $error = new \Exception();
        $event = new TechnicalErrorEvent($error);
        $this->collectApiError->expects($this->once())->method('collectFromTechnicalError')->with($error);
        $this->sut->collectTechnicalError($event);
    }

    public function test_it_flushes_collected_errors(): void
    {
        $this->collectApiError->expects($this->once())->method('flush');
        $this->sut->flushApiErrors();
    }
}

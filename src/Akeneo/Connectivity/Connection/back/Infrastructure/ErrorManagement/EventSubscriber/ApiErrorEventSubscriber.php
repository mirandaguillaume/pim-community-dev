<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\CollectApiError;
use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ProductDomainErrorEvent::class, method: 'collectProductDomainError')]
#[AsEventListener(event: ProductValidationErrorEvent::class, method: 'collectProductValidationError')]
#[AsEventListener(event: TechnicalErrorEvent::class, method: 'collectTechnicalError')]
#[AsEventListener(event: KernelEvents::TERMINATE, method: 'flushApiErrors')]
final readonly class ApiErrorEventSubscriber
{
    public function __construct(private CollectApiError $collectApiError)
    {
    }

    public function collectProductDomainError(ProductDomainErrorEvent $event): void
    {
        $this->collectApiError->collectFromProductDomainError(
            $event->getError(),
            (new Context())->setAttribute('product', $event->getProduct())
        );
    }

    public function collectProductValidationError(ProductValidationErrorEvent $event): void
    {
        $context = (new Context())->setAttribute('product', $event->getProduct());
        $this->collectApiError->collectFromProductValidationError(
            $event->getConstraintViolationList(),
            $context
        );
    }

    public function collectTechnicalError(TechnicalErrorEvent $event): void
    {
        $this->collectApiError->collectFromTechnicalError($event->getError());
    }

    public function flushApiErrors(): void
    {
        $this->collectApiError->flush();
    }
}

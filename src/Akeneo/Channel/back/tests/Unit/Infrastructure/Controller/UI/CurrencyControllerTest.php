<?php

declare(strict_types=1);

namespace Akeneo\Channel\Test\Unit\Infrastructure\Controller\UI;

use Akeneo\Channel\Infrastructure\Component\Exception\LinkedChannelException;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Channel\Infrastructure\Controller\UI\CurrencyController;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyControllerTest extends TestCase
{
    private SaverInterface|MockObject $currencySaver;
    private SecurityFacadeInterface|MockObject $securityFacade;
    private CurrencyRepositoryInterface|MockObject $currencyRepository;
    private CurrencyController $sut;

    protected function setUp(): void
    {
        $this->currencySaver = $this->createMock(SaverInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacadeInterface::class);
        $this->currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $this->sut = new CurrencyController($this->currencySaver, $this->securityFacade, $this->currencyRepository);
    }

    public function test_it_throws_access_denied_when_the_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_currency_toggle')->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $this->sut->toggleAction(1);
    }

    public function test_it_toggles_the_currency_and_saves_it(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $currency = $this->createMock(CurrencyInterface::class);
        $this->currencyRepository->method('find')->with(1)->willReturn($currency);
        $currency->expects($this->once())->method('toggleActivation');
        $this->currencySaver->expects($this->once())->method('save')->with($currency);

        $response = $this->sut->toggleAction(1);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            \json_encode(['successful' => true, 'message' => 'flash.currency.updated']),
            $response->getContent(),
        );
    }

    public function test_it_reports_failure_when_the_currency_is_linked_to_a_channel(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $currency = $this->createMock(CurrencyInterface::class);
        $this->currencyRepository->method('find')->willReturn($currency);
        $this->currencySaver->method('save')->willThrowException(new LinkedChannelException());

        $response = $this->sut->toggleAction(1);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            \json_encode(['successful' => false, 'message' => 'flash.currency.error.linked_to_channel']),
            $response->getContent(),
        );
    }
}

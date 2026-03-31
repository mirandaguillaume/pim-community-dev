<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Webhook\MessageHandler;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler\BusinessEventHandler;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessEventHandlerTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private BulkEventNormalizer|MockObject $normalizer;
    private BusinessEventHandler $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizer = $this->createMock(BulkEventNormalizer::class);
        $this->sut = new BusinessEventHandler('project_dir', $this->logger, $this->normalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BusinessEventHandler::class, $this->sut);
    }
}

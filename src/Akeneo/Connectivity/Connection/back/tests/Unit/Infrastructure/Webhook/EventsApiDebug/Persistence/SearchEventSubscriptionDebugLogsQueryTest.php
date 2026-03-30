<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\SearchEventSubscriptionDebugLogsQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionDebugLogsQueryTest extends TestCase
{
    private Client|MockObject $elasticsearchClient;
    private ClockInterface|MockObject $clock;
    private Encrypter|MockObject $encrypter;
    private SearchEventSubscriptionDebugLogsQuery $sut;

    protected function setUp(): void
    {
        $this->elasticsearchClient = $this->createMock(Client::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->encrypter = $this->createMock(Encrypter::class);
        $this->sut = new SearchEventSubscriptionDebugLogsQuery($this->elasticsearchClient, $this->clock, $this->encrypter);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SearchEventSubscriptionDebugLogsQuery::class, $this->sut);
    }

    public function test_it_throws_an_exception_when_given_level_filter_is_invalid(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->sut->execute(
            'erp',
            null,
            ['levels' => 'red'],
        );
    }

    public function test_it_throws_an_exception_when_given_timestamp_from_filter_is_invalid(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->sut->execute(
            'erp',
            null,
            ['timestamp_from' => 'not_a_correct_timestamp_from'],
        );
    }

    public function test_it_throws_an_exception_when_given_timestamp_to_filter_is_invalid(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->sut->execute(
            'erp',
            null,
            ['timestamp_to' => 'not_a_correct_timestamp_to'],
        );
    }

    public function test_it_throws_an_exception_when_given_text_filter_is_invalid(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->sut->execute(
            'erp',
            null,
            ['text' => []],
        );
    }
}

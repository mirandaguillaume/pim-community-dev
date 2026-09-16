<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\WrongCredentialsCombination\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Controller\Internal\ListWrongCredentialsCombinationsAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListWrongCredentialsCombinationsActionTest extends TestCase
{
    private WrongCredentialsCombinationRepositoryInterface|MockObject $repository;
    private ListWrongCredentialsCombinationsAction $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(WrongCredentialsCombinationRepositoryInterface::class);
        $this->sut = new ListWrongCredentialsCombinationsAction($this->repository);
    }

    public function test_it_queries_seven_days_of_wrong_credentials_combinations(): void
    {
        $this->repository->expects($this->once())
            ->method('findAll')
            ->with($this->callback(function (\DateTimeImmutable $since): bool {
                $expected = new \DateTimeImmutable('now - 7 day', new \DateTimeZone('UTC'));

                return \abs($expected->getTimestamp() - $since->getTimestamp()) < 5
                    && 'UTC' === $since->getTimezone()->getName();
            }))
            ->willReturn(new WrongCredentialsCombinations([]));

        ($this->sut)();
    }

    public function test_it_returns_the_normalized_wrong_credentials_combinations(): void
    {
        $combinations = new WrongCredentialsCombinations([
            ['connection_code' => 'erp', 'users' => ['julia' => '2026-01-01 10:00:00']],
        ]);
        $this->repository->method('findAll')->willReturn($combinations);

        $response = ($this->sut)();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(\json_encode($combinations->normalize()), $response->getContent());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\AttributeRemovalSubscriber;
use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Deleting an attribute from the UI (DELETE pim_enrich_attribute_rest_remove) relies on this subscriber to blacklist
 * the code and to launch clean_removed_attribute_job once the request terminates. The job then removes the values
 * without touching the product history, which is what display_removed_value_history.feature:8 (PIM-3420) checked.
 * ProductHistoryAfterAttributeRemovalIntegration drives the whole flow through HTTP.
 *
 * AttributeCodeBlacklister is a final class, so it is built for real on a mocked DBAL connection and the test reads
 * back the statements it sent.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRemovalSubscriberTest extends TestCase
{
    private const int JOB_EXECUTION_ID = 42;

    private JobLauncherInterface|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private JobInstance|MockObject $jobInstance;
    private UserInterface|MockObject $user;
    private EventDispatcher $eventDispatcher;
    private AttributeRemovalSubscriber $sut;

    /** @var array<int, array{sql: string, params: array<string, mixed>}> */
    private array $statements = [];

    protected function setUp(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []): int {
                $this->statements[] = ['sql' => $sql, 'params' => $params];

                return 1;
            }
        );

        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->jobInstanceRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->jobInstance = $this->createMock(JobInstance::class);
        $this->user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);
        $this->eventDispatcher = new EventDispatcher();

        $this->sut = new AttributeRemovalSubscriber(
            new AttributeCodeBlacklister($connection),
            $this->jobLauncher,
            $this->jobInstanceRepository,
            $tokenStorage,
            $this->eventDispatcher,
        );
    }

    public function test_it_ignores_the_removal_of_an_entity_that_is_not_an_attribute(): void
    {
        $this->jobLauncher->expects($this->never())->method('launch');

        $this->sut->blacklistAttributeCodeAndLaunchJob(new GenericEvent(new \stdClass()));

        $this->assertSame([], $this->statements);
        $this->assertSame([], $this->eventDispatcher->getListeners(KernelEvents::TERMINATE));
        $this->assertSame([], $this->eventDispatcher->getListeners(ConsoleEvents::TERMINATE));
    }

    public function test_it_blacklists_the_removed_attributes_and_launches_one_clean_job_when_the_request_terminates(): void
    {
        $this->sut->blacklistAttributeCodeAndLaunchJob(new GenericEvent($this->attribute('weather_conditions')));
        $this->sut->blacklistAttributeCodeAndLaunchJob(new GenericEvent($this->attribute('lace_color')));

        $this->assertSame(['weather_conditions', 'lace_color'], $this->blacklistedCodes());
        // Registered once, whatever the number of removals.
        $this->assertCount(1, $this->eventDispatcher->getListeners(KernelEvents::TERMINATE));
        $this->assertCount(1, $this->eventDispatcher->getListeners(ConsoleEvents::TERMINATE));
        $this->assertSame([], $this->registeredJobs(), 'No job may be launched before the request terminates.');

        $this->expectCleanJobLaunchedOnceFor(['weather_conditions', 'lace_color']);
        $this->eventDispatcher->dispatch(new \stdClass(), KernelEvents::TERMINATE);

        $this->assertSame(
            [['attribute_codes' => ['weather_conditions', 'lace_color'], 'job_execution_id' => self::JOB_EXECUTION_ID]],
            $this->registeredJobs()
        );

        // The codes were handed to the job: a later terminate event launches nothing more.
        $this->eventDispatcher->dispatch(new \stdClass(), ConsoleEvents::TERMINATE);
        $this->assertCount(1, $this->registeredJobs());
    }

    public function test_it_launches_the_clean_job_right_away_when_1000_attributes_are_removed(): void
    {
        $codes = array_map(static fn (int $index): string => sprintf('attribute_%d', $index), range(1, 1000));
        $this->expectCleanJobLaunchedOnceFor($codes);

        foreach ($codes as $code) {
            $this->sut->blacklistAttributeCodeAndLaunchJob(new GenericEvent($this->attribute($code)));
        }

        $this->assertSame([['attribute_codes' => $codes, 'job_execution_id' => self::JOB_EXECUTION_ID]], $this->registeredJobs());

        $this->eventDispatcher->dispatch(new \stdClass(), KernelEvents::TERMINATE);
        $this->assertCount(1, $this->registeredJobs());
    }

    /**
     * @param string[] $attributeCodes
     */
    private function expectCleanJobLaunchedOnceFor(array $attributeCodes): void
    {
        $this->jobInstanceRepository->expects($this->once())
            ->method('findOneByIdentifier')
            ->with('clean_removed_attribute_job')
            ->willReturn($this->jobInstance);

        $jobExecution = $this->createMock(JobExecution::class);
        $jobExecution->method('getId')->willReturn(self::JOB_EXECUTION_ID);
        $this->jobLauncher->expects($this->once())
            ->method('launch')
            ->with($this->identicalTo($this->jobInstance), $this->identicalTo($this->user), ['attribute_codes' => $attributeCodes])
            ->willReturn($jobExecution);
    }

    private function attribute(string $code): AttributeInterface
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getCode')->willReturn($code);

        return $attribute;
    }

    /**
     * @return string[]
     */
    private function blacklistedCodes(): array
    {
        $codes = [];
        foreach ($this->statements as $statement) {
            if (str_contains($statement['sql'], 'INSERT INTO `pim_catalog_attribute_blacklist`')) {
                $codes = [...$codes, ...array_values($statement['params'])];
            }
        }

        return $codes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function registeredJobs(): array
    {
        return array_values(array_map(
            static fn (array $statement): array => $statement['params'],
            array_filter(
                $this->statements,
                static fn (array $statement): bool => str_contains($statement['sql'], 'SET `cleanup_job_execution_id`')
            )
        ));
    }
}

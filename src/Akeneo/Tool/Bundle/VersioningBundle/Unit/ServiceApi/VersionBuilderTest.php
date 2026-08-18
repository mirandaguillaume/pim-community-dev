<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\ServiceApi;

use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder as LegacyVersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilderTest extends TestCase
{
    private const RESOURCE_NAME = 'Akeneo\Category\Infrastructure\Component\Model\Category';

    private VersionFactory|MockObject $versionFactory;
    private VersionRepository|MockObject $versionRepository;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private ObjectManager|MockObject $objectManager;
    private LegacyVersionBuilder|MockObject $versionBuilder;
    private VersionBuilder $sut;

    /** @var Version[] versions handed out by the version factory, in creation order */
    private array $createdVersions = [];

    /** @var array<int, array> arguments received by each VersionFactory::create() call */
    private array $createArguments = [];

    /** @var array arguments received by VersionRepository::getNewestLogEntry() */
    private array $newestLogEntryArguments = [];

    protected function setUp(): void
    {
        $this->versionFactory = $this->createMock(VersionFactory::class);
        $this->versionRepository = $this->createMock(VersionRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->versionBuilder = $this->createMock(LegacyVersionBuilder::class);

        $this->versionFactory
            ->method('create')
            ->willReturnCallback(function (...$arguments): Version {
                $this->createArguments[] = $arguments;
                $version = new Version(
                    $arguments[0],
                    $arguments[1],
                    $arguments[2],
                    $arguments[3],
                    $arguments[4] ?? null
                );
                $this->createdVersions[] = $version;

                return $version;
            });

        $this->sut = new VersionBuilder(
            $this->versionFactory,
            $this->versionRepository,
            $this->eventDispatcher,
            $this->objectManager,
            $this->versionBuilder
        );
    }

    public function test_it_creates_a_first_version_when_no_previous_version_exists(): void
    {
        $snapshot = ['code' => 'socks', 'label-en_US' => 'Socks'];
        $changeset = ['code' => ['old' => '', 'new' => 'socks']];

        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder
            ->expects($this->once())
            ->method('buildChangeset')
            ->with([], $snapshot)
            ->willReturn($changeset);

        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->never())->method('remove');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, $snapshot);

        $this->assertCount(1, $this->createdVersions);
        $version = $this->createdVersions[0];
        $this->assertSame(1, $version->getVersion());
        $this->assertSame($snapshot, $version->getSnapshot());
        $this->assertSame($changeset, $version->getChangeset());
        $this->assertSame(self::RESOURCE_NAME, $version->getResourceName());
        $this->assertSame('42', $version->getResourceId());
        $this->assertNull($version->getResourceUuid());

        $this->assertSame(self::RESOURCE_NAME, $this->newestLogEntryArguments[0]);
        $this->assertSame('42', $this->newestLogEntryArguments[1]);
        $this->assertNull($this->newestLogEntryArguments[2]);
    }

    public function test_it_increments_the_version_number_of_the_newest_log_entry(): void
    {
        $oldSnapshot = ['code' => 'socks', 'label-en_US' => 'Socks'];
        $newSnapshot = ['code' => 'socks', 'label-en_US' => 'Blue socks'];
        $changeset = ['label-en_US' => ['old' => 'Socks', 'new' => 'Blue socks']];

        $previousVersion = new Version(self::RESOURCE_NAME, '42', null, 'admin')
            ->setVersion(7)
            ->setSnapshot($oldSnapshot);

        $this->stubNewestLogEntry($previousVersion);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder
            ->expects($this->once())
            ->method('buildChangeset')
            ->with($oldSnapshot, $newSnapshot)
            ->willReturn($changeset);

        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->never())->method('remove');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, $newSnapshot);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame(8, $this->createdVersions[0]->getVersion());
        $this->assertSame($newSnapshot, $this->createdVersions[0]->getSnapshot());
        $this->assertSame($changeset, $this->createdVersions[0]->getChangeset());
    }

    public function test_it_uses_the_username_provided_by_the_pre_build_event(): void
    {
        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername('julia');
        $this->versionBuilder->method('buildChangeset')->willReturn(['code' => ['old' => '', 'new' => 'socks']]);

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, ['code' => 'socks']);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame('julia', $this->createdVersions[0]->getAuthor());
    }

    public function test_it_falls_back_on_the_default_system_user_when_the_event_provides_no_username(): void
    {
        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder->method('buildChangeset')->willReturn(['code' => ['old' => '', 'new' => 'socks']]);

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, ['code' => 'socks']);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame(VersionManager::DEFAULT_SYSTEM_USER, $this->createdVersions[0]->getAuthor());
    }

    public function test_it_removes_the_version_instead_of_persisting_it_when_the_changeset_is_empty(): void
    {
        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder->method('buildChangeset')->willReturn([]);

        $removed = [];
        $this->objectManager->expects($this->never())->method('persist');
        $this->objectManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->capture($removed));
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, ['code' => 'socks']);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame([$this->createdVersions[0]], \array_values($removed));
    }

    public function test_it_creates_a_dedicated_version_holding_the_updated_date_when_the_changeset_contains_a_permission(): void
    {
        $snapshot = ['code' => 'socks', 'view_permission' => 'all'];
        $changeset = [
            'view_permission' => ['old' => 'none', 'new' => 'all'],
            'updated' => ['old' => '2023-01-01', 'new' => '2023-01-02'],
        ];

        $previousVersion = new Version(self::RESOURCE_NAME, '42', null, 'admin')
            ->setVersion(3)
            ->setSnapshot(['code' => 'socks']);

        $this->stubNewestLogEntry($previousVersion);
        $this->stubPreBuildEventUsername('julia');
        $this->versionBuilder->method('buildChangeset')->willReturn($changeset);

        $persisted = [];
        $this->objectManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->capture($persisted));
        $this->objectManager->expects($this->never())->method('remove');
        $this->objectManager->expects($this->exactly(2))->method('flush');

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, $snapshot);

        $this->assertCount(2, $this->createdVersions);
        [$mainVersion, $dedicatedVersion] = $this->createdVersions;

        $this->assertSame(4, $mainVersion->getVersion());
        $this->assertSame(
            ['view_permission' => ['old' => 'none', 'new' => 'all']],
            $mainVersion->getChangeset(),
            'The updated date must be moved out of the main version changeset'
        );

        $this->assertSame(5, $dedicatedVersion->getVersion());
        $this->assertSame(
            ['updated' => ['old' => '2023-01-01', 'new' => '2023-01-02']],
            $dedicatedVersion->getChangeset()
        );
        $this->assertSame($snapshot, $dedicatedVersion->getSnapshot());
        $this->assertSame(self::RESOURCE_NAME, $dedicatedVersion->getResourceName());
        $this->assertSame('42', $dedicatedVersion->getResourceId());
        $this->assertSame('julia', $dedicatedVersion->getAuthor());

        $this->assertSame([$mainVersion, $dedicatedVersion], \array_values($persisted));
    }

    public function test_it_does_not_create_a_dedicated_version_when_the_resource_is_being_created(): void
    {
        $changeset = [
            'view_permission' => ['old' => '', 'new' => 'all'],
            'updated' => ['old' => '', 'new' => '2023-01-02'],
        ];

        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder->method('buildChangeset')->willReturn($changeset);

        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->never())->method('remove');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->buildVersionWithId(null, self::RESOURCE_NAME, ['code' => 'socks']);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame($changeset, $this->createdVersions[0]->getChangeset());
        $this->assertNull($this->createdVersions[0]->getResourceId());
    }

    public function test_it_does_not_create_a_dedicated_version_when_no_changeset_key_ends_with_permission(): void
    {
        $changeset = [
            'permission' => ['old' => 'none', 'new' => 'all'],
            'permissions' => ['old' => 'none', 'new' => 'all'],
            'updated' => ['old' => '2023-01-01', 'new' => '2023-01-02'],
        ];

        $this->stubNewestLogEntry(null);
        $this->stubPreBuildEventUsername(null);
        $this->versionBuilder->method('buildChangeset')->willReturn($changeset);

        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->never())->method('remove');
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->buildVersionWithId('42', self::RESOURCE_NAME, ['code' => 'socks']);

        $this->assertCount(1, $this->createdVersions);
        $this->assertSame($changeset, $this->createdVersions[0]->getChangeset());
    }

    private function stubNewestLogEntry(?Version $previousVersion): void
    {
        $this->versionRepository
            ->expects($this->once())
            ->method('getNewestLogEntry')
            ->willReturnCallback(function (...$arguments) use ($previousVersion): ?Version {
                $this->newestLogEntryArguments = $arguments;

                return $previousVersion;
            });
    }

    private function stubPreBuildEventUsername(?string $username): void
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BuildVersionEvent::class), BuildVersionEvents::PRE_BUILD)
            ->willReturnCallback(function (BuildVersionEvent $event) use ($username): BuildVersionEvent {
                if (null !== $username) {
                    $event->setUsername($username);
                }

                return $event;
            });
    }

    /**
     * Records every object the constraint is evaluated against, keyed by object id so that a
     * constraint evaluated more than once for a single invocation does not duplicate the entry.
     */
    private function capture(array &$captured): \PHPUnit\Framework\Constraint\Callback
    {
        return $this->callback(function (object $object) use (&$captured): bool {
            $captured[\spl_object_id($object)] = $object;

            return true;
        });
    }
}

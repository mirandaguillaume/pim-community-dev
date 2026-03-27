<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Query\InMemoryFindViewedAnnouncementIds;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository\InMemoryViewedAnnouncementRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HasNewAnnouncementsHandlerTest extends TestCase
{
    private FindNewAnnouncementIdsInterface|MockObject $findNewAnnouncementIds;
    private HasNewAnnouncementsHandler $sut;

    private InMemoryViewedAnnouncementRepository $viewedAnnouncementsRepository;

    protected function setUp(): void
    {
        $this->findNewAnnouncementIds = $this->createMock(FindNewAnnouncementIdsInterface::class);
        $this->viewedAnnouncementsRepository = new InMemoryViewedAnnouncementRepository();
        $findViewedAnnouncementIds = new InMemoryFindViewedAnnouncementIds($this->viewedAnnouncementsRepository);
        $this->sut = new HasNewAnnouncementsHandler($this->findNewAnnouncementIds, $findViewedAnnouncementIds);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HasNewAnnouncementsHandler::class, $this->sut);
    }

    public function test_it_returns_true_if_it_has_new_announcements_not_seen_by_the_user(): void
    {
        $userId = 1;
        $edition = 'Serenity';
        $locale = 'en_US';
        $version = '20201015';
        $query = new HasNewAnnouncementsQuery($edition, $version, $locale, $userId);
        $this->viewedAnnouncementsRepository->dataRows[] = ['announcement_id' => 'new_announcement_viewed', 'user_id' => $userId];
        $this->findNewAnnouncementIds->method('find')->with($edition, $version, $locale)->willReturn(['new_announcement_viewed', 'other_new_announcement']);
        $this->assertSame(true, $this->sut->execute($query));
    }

    public function test_it_returns_false_if_it_has_only_new_announcements_already_seen_by_the_user(): void
    {
        $userId = 1;
        $edition = 'Serenity';
        $version = '20201015';
        $locale = 'en_US';
        $query = new HasNewAnnouncementsQuery($edition, $version, $locale, $userId);
        $this->viewedAnnouncementsRepository->dataRows
                = [
                    [
                        'announcement_id' => 'new_announcement_viewed',
                        'user_id' => $userId,
                    ],
                    [
                        'announcement_id' => 'other_new_announcement_viewed',
                        'user_id' => $userId,
                    ],
                ];
        $this->findNewAnnouncementIds->method('find')->with($edition, $version, $locale)->willReturn(['new_announcement_viewed', 'other_new_announcement_viewed']);
        $this->assertSame(false, $this->sut->execute($query));
    }
}

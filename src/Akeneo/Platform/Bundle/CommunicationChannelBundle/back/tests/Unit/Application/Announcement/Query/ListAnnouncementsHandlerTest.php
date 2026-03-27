<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Query\InMemoryFindViewedAnnouncementIds;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository\InMemoryViewedAnnouncementRepository;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListAnnouncementsHandlerTest extends TestCase
{
    private FindAnnouncementItemsInterface|MockObject $findAnnouncementItems;
    private InMemoryViewedAnnouncementRepository $viewedAnnouncementsRepository;
    private ListAnnouncementsHandler $sut;

    protected function setUp(): void
    {
        $this->findAnnouncementItems = $this->createMock(FindAnnouncementItemsInterface::class);
        $this->viewedAnnouncementsRepository = new InMemoryViewedAnnouncementRepository();
        $findViewedAnnouncementIds = new InMemoryFindViewedAnnouncementIds($this->viewedAnnouncementsRepository);
        $this->sut = new ListAnnouncementsHandler($this->findAnnouncementItems, $findViewedAnnouncementIds);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ListAnnouncementsHandler::class, $this->sut);
    }

    public function test_it_handles_the_list_paginated_announcements_query(): void
    {
        $announcementItems = $this->getAnnouncements(new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2020-01-02'));
        $this->findAnnouncementItems->method('byPimVersion')->with(
            $this->isType('string'),
            $this->isType('string'),
            $this->isType('string'),
            $this->isType('string')
        )->willReturn([$announcementItems[1]]);
        $query = new ListAnnouncementsQuery('EE', '4.0', 'en_US', 1, 'f68a21bb-ec9a-4009-9b0b-2639c6798e5f');
        $this->assertSame([$announcementItems[1]], $this->sut->execute($query));
    }

    public function test_it_notifies_new_announcements_when_user_has_not_seen_it(): void
    {
        $startDate = new \DateTimeImmutable();
        $endDate = new \DateTimeImmutable('tomorrow');
        $announcementItems = $this->getAnnouncements($startDate, $endDate);
        $this->findAnnouncementItems->method('byPimVersion')->with(
            $this->isType('string'),
            $this->isType('string'),
            $this->isType('string'),
            null
        )->willReturn($announcementItems);
        $this->viewedAnnouncementsRepository->dataRows[] = ['announcement_id' => 'update-easily_monitor_errors_on_your_connections-2020-06-04', 'user_id' => 1];
        $query = new ListAnnouncementsQuery('EE', '4.0', 'en_US', 1, null);
        $this->assertEquals([
            new AnnouncementItem(
                'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'Easily monitor errors on your connections',
                'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'Connection monitoring page',
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                $startDate,
                $endDate,
                ['updates']
            ),
            new AnnouncementItem(
                'update-new_metrics_on_the_connection_dashboard-2020-06-04',
                'New metrics on the Connection dashboard',
                'The Connection dashboard now displays additional information to ease error monitoring and allow you to see at a glance how your source connections are performing.',
                null,
                null,
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#new-metrics-on-the-connection-dashboard',
                $startDate,
                $endDate,
                ['updates', 'new']
            ),
        ], $this->sut->execute($query));
    }

    private function getAnnouncements(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return [
            new AnnouncementItem(
                'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'Easily monitor errors on your connections',
                'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'Connection monitoring page',
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                $startDate,
                $endDate,
                ['updates']
            ),
            new AnnouncementItem(
                'update-new_metrics_on_the_connection_dashboard-2020-06-04',
                'New metrics on the Connection dashboard',
                'The Connection dashboard now displays additional information to ease error monitoring and allow you to see at a glance how your source connections are performing.',
                null,
                null,
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#new-metrics-on-the-connection-dashboard',
                $startDate,
                $endDate,
                ['updates']
            ),
        ];
    }
}

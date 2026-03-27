<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AnnouncementItemTest extends TestCase
{
    private AnnouncementItem $sut;

    protected function setUp(): void {}

    public function test_it_normalizes_itself(): void
    {
        $this->sut = new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020-10-01'),
            new \DateTimeImmutable('2020-10-06'),
            ['updates']
        );
        $this->assertTrue(is_a(AnnouncementItem::class, AnnouncementItem::class, true));
        $this->assertSame([
            'id' => 'id',
            'title' => 'Title',
            'description' => 'Description',
            'img' => '/images/announcements/new-connection-monitoring-page.png',
            'altImg' => 'Connection monitoring page',
            'link' => 'http://link.com#easily-monitor-errors-on-your-connections',
            'startDate' => 'October, 1st 2020',
            'tags' => ['updates'],
        ], $this->sut->toArray());
    }

    public function test_it_adds_new_as_tag_when_we_notify_the_announcement(): void
    {
        $this->sut = new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020-10-20'),
            new \DateTimeImmutable('2020-10-30'),
            ['updates']
        );
        $this->assertEquals(new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020-10-20'),
            new \DateTimeImmutable('2020-10-30'),
            ['updates', 'new']
        ), $this->sut->toNotify());
    }

    public function test_it_should_be_notified_when_the_announcement_is_new_and_not_already_viewed(): void
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $this->sut = new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $tomorrow,
            ['updates']
        );
        $this->assertSame(true, $this->sut->shouldBeNotified(['id_2']));
    }

    public function test_it_should_not_be_notify_when_the_announcement_is_already_viewed(): void
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $this->sut = new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $tomorrow,
            ['updates']
        );
        $this->assertSame(false, $this->sut->shouldBeNotified(['id', 'id_2']));
    }

    public function test_it_should_not_be_notify_when_the_announcement_end_date_is_after_the_current_date(): void
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $this->sut = new AnnouncementItem(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $yesterday,
            ['updates']
        );
        $this->assertSame(false, $this->sut->shouldBeNotified(['id_2']));
    }
}

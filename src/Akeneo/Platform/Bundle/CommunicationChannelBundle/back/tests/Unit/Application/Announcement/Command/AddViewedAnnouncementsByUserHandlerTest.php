<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Platform\CommunicationChannel\Application\Announcement\Command;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserCommand;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserHandler;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Repository\ViewedAnnouncementRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddViewedAnnouncementsByUserHandlerTest extends TestCase
{
    private ViewedAnnouncementRepositoryInterface|MockObject $viewedAnnouncementRepository;
    private AddViewedAnnouncementsByUserHandler $sut;

    protected function setUp(): void
    {
        $this->viewedAnnouncementRepository = $this->createMock(ViewedAnnouncementRepositoryInterface::class);
        $this->sut = new AddViewedAnnouncementsByUserHandler($this->viewedAnnouncementRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddViewedAnnouncementsByUserHandler::class, $this->sut);
    }

    public function test_it_handles_the_add_viewed_announcements_by_user(): void
    {
        $command = new AddViewedAnnouncementsByUserCommand(
            ['announcement_id_1', 'announcement_id_2'],
            1
        );
        $this->viewedAnnouncementRepository->expects($this->once())->method('create')->with($this->isType('array'));
        $this->sut->execute($command);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\StepExecutionArchivist;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StepExecutionArchivistTest extends TestCase
{
    private StepExecutionArchivist $sut;

    protected function setUp(): void
    {
        $this->sut = new StepExecutionArchivist();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(StepExecutionArchivist::class, $this->sut);
    }

    public function test_it_throws_an_exception_if_there_is_already_a_registered_archiver(): void
    {
        $archiver = $this->createMock(ArchiverInterface::class);

        $archiver->method('getName')->willReturn('output');
        $this->sut->registerArchiver($archiver);
        $this->expectException('\InvalidArgumentException');
        $this->sut->registerArchiver($archiver);
    }

    public function test_it_returns_generated_archives(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver = $this->createMock(ArchiverInterface::class);
        $archiver2 = $this->createMock(ArchiverInterface::class);
        $archiver3 = $this->createMock(ArchiverInterface::class);

        $jobExecution->method('isRunning')->willReturn(false);
        $archiver->method('getName')->willReturn('output');
        $archiver->method('getArchives')->willReturn(['log.log' => 'a/b/log.log', 'test.png' => 'a/b/test.png']);
        $this->sut->registerArchiver($archiver);
        $archiver2->method('getName')->willReturn('input');
        $archiver2->method('getArchives')->willReturn(['image.jpg' => 'a/c/d/image.jpg', 'notice.pdf' => 'b/c/d/notice.pdf']);
        $this->sut->registerArchiver($archiver2);
        $archiver3->method('getName')->willReturn('invalid_items');
        $archiver3->method('getArchives')->willReturn([]);
        $this->sut->registerArchiver($archiver3);
        $archives = $this->sut->getArchives($jobExecution);
        $this->assertIsArray($archives);
        $this->assertArrayHasKey('output', $archives);
        $this->assertArrayHasKey('input', $archives);
        $this->assertArrayHasKey('invalid_items', $archives);
    }

    public function test_it_does_not_return_archives_if_the_job_is_still_running(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver = $this->createMock(ArchiverInterface::class);

        $archiver->method('getName')->willReturn('output');
        $this->sut->registerArchiver($archiver);
        $jobExecution->method('isRunning')->willReturn(true);
        $archiver->expects($this->never())->method('getArchives');
        $this->assertSame([], $this->sut->getArchives($jobExecution));
    }

    public function test_it_throws_an_exception_if_no_archiver_is_defined(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver = $this->createMock(ArchiverInterface::class);

        $archiver->method('getName')->willReturn('archiver');
        $this->sut->registerArchiver($archiver);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->getArchive($jobExecution, 'archiver_name', 'key');
    }

    public function test_it_returns_the_corresponding_archiver(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver = $this->createMock(ArchiverInterface::class);

        $archiver->method('getName')->willReturn('output');
        $archiver->expects($this->once())->method('getArchive')->with($jobExecution, 'key');
        $this->sut->registerArchiver($archiver);
        $this->sut->getArchive($jobExecution, 'output', 'key');
    }

    public function test_it_register_an_event_and_verify_if_job_is_supported(): void
    {
        $event = $this->createMock(StepExecutionEvent::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $archiver1 = $this->createMock(ArchiverInterface::class);
        $archiver2 = $this->createMock(ArchiverInterface::class);

        $archiver1->method('getName')->willReturn('archiver_1');
        $archiver2->method('getName')->willReturn('archiver_2');
        $this->sut->registerArchiver($archiver1);
        $this->sut->registerArchiver($archiver2);
        $event->method('getStepExecution')->willReturn($stepExecution);
        $archiver1->method('supports')->with($stepExecution)->willReturn(true);
        $archiver2->method('supports')->with($stepExecution)->willReturn(false);
        $archiver1->expects($this->once())->method('archive')->with($stepExecution);
        $archiver2->expects($this->never())->method('archive')->with($stepExecution);
        $this->sut->onStepExecutionCompleted($event);
    }

    public function test_it_tells_there_are_no_two_archives_when_all_empty(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver1 = $this->createMock(ArchiverInterface::class);
        $archiver2 = $this->createMock(ArchiverInterface::class);
        $archiver3 = $this->createMock(ArchiverInterface::class);

        $archiver1->method('getName')->willReturn('output');
        $this->sut->registerArchiver($archiver1);
        $archiver2->method('getName')->willReturn('media');
        $this->sut->registerArchiver($archiver2);
        $archiver3->method('getName')->willReturn('jobs');
        $this->sut->registerArchiver($archiver3);
        $jobExecution->method('isRunning')->willReturn(false);
        $archiver1->method('getArchives')->willReturn([]);
        $archiver2->method('getArchives')->willReturn([]);
        $archiver3->method('getArchives')->willReturn([]);
        $this->assertSame(false, $this->sut->hasAtLeastTwoArchives($jobExecution));
    }

    public function test_it_tells_there_are_at_least_two_archives_when_enough_exist(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $archiver1 = $this->createMock(ArchiverInterface::class);
        $archiver2 = $this->createMock(ArchiverInterface::class);
        $archiver3 = $this->createMock(ArchiverInterface::class);

        $archiver1->method('getName')->willReturn('output');
        $this->sut->registerArchiver($archiver1);
        $archiver2->method('getName')->willReturn('media');
        $this->sut->registerArchiver($archiver2);
        $archiver3->method('getName')->willReturn('jobs');
        $this->sut->registerArchiver($archiver3);
        $jobExecution->method('isRunning')->willReturn(false);
        $archiver1->method('getArchives')->willReturn(['file1' => 'path1', 'file2' => 'path2']);
        $archiver2->method('getArchives')->willReturn([]);
        $archiver3->method('getArchives')->willReturn([]);
        $this->assertSame(true, $this->sut->hasAtLeastTwoArchives($jobExecution));
    }
}

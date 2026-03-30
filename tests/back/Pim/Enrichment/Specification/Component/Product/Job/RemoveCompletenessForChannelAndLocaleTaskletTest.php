<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Job;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Job\RemoveCompletenessForChannelAndLocaleTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class RemoveCompletenessForChannelAndLocaleTaskletTest extends TestCase
{
    private EntityManagerClearerInterface|MockObject $cacheClearer;
    private NotifierInterface|MockObject $notifier;
    private SimpleFactoryInterface|MockObject $notificationFactory;
    private ProductQueryBuilderFactoryInterface|MockObject $productQueryBuilderFactory;
    private ProductRepositoryInterface|MockObject $productRepository;
    private ChannelRepositoryInterface|MockObject $channelRepository;
    private BulkSaverInterface|MockObject $productBulkSaver;
    private StepExecution|MockObject $stepExecution;
    private JobParameters|MockObject $jobParameters;
    private RemoveCompletenessForChannelAndLocaleTasklet $sut;

    protected function setUp(): void
    {
        $this->cacheClearer = $this->createMock(EntityManagerClearerInterface::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->notificationFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->productQueryBuilderFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->productBulkSaver = $this->createMock(BulkSaverInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobParameters = $this->createMock(JobParameters::class);
        $this->sut = new RemoveCompletenessForChannelAndLocaleTasklet($this->cacheClearer,
            $this->notifier,
            $this->notificationFactory,
            $this->productQueryBuilderFactory,
            $this->productRepository,
            $this->channelRepository,
            $this->productBulkSaver,
            2);
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $frFr = new Locale();
        $frFr->setCode('fr_FR');
        $esEs = new Locale();
        $esEs->setCode('es_ES');
        $channel = new Channel();
        $channel->addLocale($enUS);
        $channel->addLocale($frFr);
        $channel->addLocale($esEs);
        $this->channelRepository->method('findOneByIdentifier')->with('ecommerce')->willReturn($channel);
        $this->channelRepository->method('findOneByIdentifier')->with(/* TODO: convert Argument matcher */ Argument::not('ecommerce'))->willReturn(null);
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);
        $this->sut->setStepExecution($this->stepExecution);
    }

}

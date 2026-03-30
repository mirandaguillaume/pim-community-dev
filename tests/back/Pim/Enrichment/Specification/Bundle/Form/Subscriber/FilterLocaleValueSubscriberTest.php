<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Form\Subscriber;

use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleValueSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class FilterLocaleValueSubscriberTest extends TestCase
{
    private FilterLocaleValueSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new FilterLocaleValueSubscriber();
    }

}

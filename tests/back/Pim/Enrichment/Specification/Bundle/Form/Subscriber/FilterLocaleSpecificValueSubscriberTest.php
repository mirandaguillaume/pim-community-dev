<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Form\Subscriber;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class FilterLocaleSpecificValueSubscriberTest extends TestCase
{
    private FilterLocaleSpecificValueSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new FilterLocaleSpecificValueSubscriber();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\FollowUp\ReadModel;

use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use PHPUnit\Framework\TestCase;

class CompletenessWidgetTest extends TestCase
{
    private CompletenessWidget $sut;

    protected function setUp(): void
    {
        $this->sut = new CompletenessWidget();
    }

}

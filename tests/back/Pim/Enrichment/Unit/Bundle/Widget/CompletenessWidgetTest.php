<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Widget;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\TestCase;

class CompletenessWidgetTest extends TestCase
{
    private CompletenessWidget $sut;

    protected function setUp(): void
    {
        $this->sut = new CompletenessWidget();
    }

}

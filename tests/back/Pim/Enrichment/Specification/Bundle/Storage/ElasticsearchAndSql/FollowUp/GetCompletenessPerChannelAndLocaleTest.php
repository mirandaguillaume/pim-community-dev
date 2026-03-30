<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\FollowUp\Query;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCompletenessPerChannelAndLocaleTest extends TestCase
{
    private GetCompletenessPerChannelAndLocale $sut;

    protected function setUp(): void
    {
        $this->sut = new GetCompletenessPerChannelAndLocale();
    }

}

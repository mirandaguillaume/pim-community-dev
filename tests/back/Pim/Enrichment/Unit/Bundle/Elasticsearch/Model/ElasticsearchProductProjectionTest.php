<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchProductProjectionTest extends TestCase
{
    private ElasticsearchProductProjection $sut;

    protected function setUp(): void
    {
        $this->sut = new ElasticsearchProductProjection();
    }

}

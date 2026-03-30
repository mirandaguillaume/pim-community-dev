<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\PdfGeneration\HtmlFormatter;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\HtmlFormatter\ArabicHtmlFormatter;
use PHPUnit\Framework\TestCase;

class ArabicHtmlFormatterTest extends TestCase
{
    private ArabicHtmlFormatter $sut;

    protected function setUp(): void
    {
        $this->sut = new ArabicHtmlFormatter();
    }

}

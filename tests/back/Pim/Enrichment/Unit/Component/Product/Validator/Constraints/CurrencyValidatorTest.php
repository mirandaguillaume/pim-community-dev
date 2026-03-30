<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\CurrencyValidator;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CurrencyValidatorTest extends TestCase
{
    private CurrencyValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyValidator();
    }

}

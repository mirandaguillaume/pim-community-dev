<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use PHPUnit\Framework\TestCase;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiErrorCollectionTest extends TestCase
{
    private ApiErrorCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new ApiErrorCollection();
    }

    public function test_it_is_instantiated_with_valid_error_types(): void
    {
        $sorted = $this->sut->getSorted();
        $this->assertArrayHasKey(ErrorTypes::BUSINESS, $sorted);
        $this->assertArrayHasKey(ErrorTypes::TECHNICAL, $sorted);
    }

    public function test_it_can_be_constructed_with_initial_errors(): void
    {
        $businessError = new BusinessError('{"message": "error"}');
        $this->sut = new ApiErrorCollection([$businessError]);
        $this->assertSame(1, $this->sut->count(ErrorTypes::BUSINESS));
    }

    public function test_it_adds_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->assertSame(0, $this->sut->count());
        $this->sut->add($businessErrorA);
        $this->assertSame(1, $this->sut->count());
        $this->sut->add($businessErrorB);
        $this->assertSame(2, $this->sut->count());
        $this->sut->add($technicalErrorA);
        $this->assertSame(3, $this->sut->count());
        $this->sut->add($technicalErrorB);
        $this->assertSame(4, $this->sut->count());
    }

    public function test_it_adds_and_sorts_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->assertSame(0, $this->sut->count(ErrorTypes::BUSINESS));
        $this->assertSame(0, $this->sut->count(ErrorTypes::TECHNICAL));
        $this->sut->add($businessErrorA);
        $this->assertSame(1, $this->sut->count(ErrorTypes::BUSINESS));
        $this->assertSame(0, $this->sut->count(ErrorTypes::TECHNICAL));
        $this->sut->add($businessErrorB);
        $this->assertSame(2, $this->sut->count(ErrorTypes::BUSINESS));
        $this->assertSame(0, $this->sut->count(ErrorTypes::TECHNICAL));
        $this->sut->add($technicalErrorA);
        $this->assertSame(2, $this->sut->count(ErrorTypes::BUSINESS));
        $this->assertSame(1, $this->sut->count(ErrorTypes::TECHNICAL));
        $this->sut->add($technicalErrorB);
        $this->assertSame(2, $this->sut->count(ErrorTypes::BUSINESS));
        $this->assertSame(2, $this->sut->count(ErrorTypes::TECHNICAL));
    }

    public function test_it_provides_api_errors_by_type(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->sut = new ApiErrorCollection([$businessErrorA, $businessErrorB, $technicalErrorA, $technicalErrorB]);
        $this->assertSame([$businessErrorA, $businessErrorB], $this->sut->getByType(ErrorTypes::BUSINESS));
        $this->assertSame([$technicalErrorA, $technicalErrorB], $this->sut->getByType(ErrorTypes::TECHNICAL));
    }

    public function test_it_provides_all_sorted_api_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->sut = new ApiErrorCollection([$businessErrorA, $businessErrorB, $technicalErrorA, $technicalErrorB]);
        $this->assertSame([
                        ErrorTypes::BUSINESS =>  [$businessErrorA, $businessErrorB],
                        ErrorTypes::TECHNICAL => [$technicalErrorA, $technicalErrorB],
                    ], $this->sut->getSorted());
    }

    public function test_it_accepts_only_api_errors_as_initial_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Class "%s" accepts only "%s" in the collection.',
            ApiErrorCollection::class,
            ApiErrorInterface::class
        ));
        new ApiErrorCollection([new \DateTime()]);
    }

    public function test_it_does_not_accept_to_provide_errors_by_type_it_does_not_know(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->getByType('any_type');
    }

    public function test_it_does_not_accept_to_count_error_types_it_does_not_know(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->count('any_type');
    }
}

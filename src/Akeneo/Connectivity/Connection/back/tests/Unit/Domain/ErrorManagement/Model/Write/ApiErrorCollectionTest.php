<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

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
    }

    public function test_it_is_instantiated_with_valid_error_types(): void
    {
        $this->sut->getSorted()->shouldMatchErrorTypes();
    }

    public function test_it_can_be_constructed_with_initial_errors(): void
    {
        $businessError = new BusinessError('{"message": "error"}');
        $this->sut = new ApiErrorCollection([$businessError]);
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(1);
    }

    public function test_it_adds_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->sut->count()->shouldBeEqualTo(0);
        $this->sut->add($businessErrorA);
        $this->sut->count()->shouldBeEqualTo(1);
        $this->sut->add($businessErrorB);
        $this->sut->count()->shouldBeEqualTo(2);
        $this->sut->add($technicalErrorA);
        $this->sut->count()->shouldBeEqualTo(3);
        $this->sut->add($technicalErrorB);
        $this->sut->count()->shouldBeEqualTo(4);
    }

    public function test_it_adds_and_sorts_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(0);
        $this->sut->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);
        $this->sut->add($businessErrorA);
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(1);
        $this->sut->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);
        $this->sut->add($businessErrorB);
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->sut->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);
        $this->sut->add($technicalErrorA);
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->sut->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(1);
        $this->sut->add($technicalErrorB);
        $this->sut->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->sut->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(2);
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
        $this->expectException(new \InvalidArgumentException(
            \sprintf(
                'Class "%s" accepts only "%s" in the collection.',
                ApiErrorCollection::class,
                ApiErrorInterface::class
            )
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

    // TODO: Custom matchers from getMatchers() need manual conversion
}

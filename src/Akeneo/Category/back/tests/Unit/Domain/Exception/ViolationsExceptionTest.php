<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Domain\Exception;

use Akeneo\Category\Domain\Exception\ViolationsException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationsExceptionTest extends TestCase
{
    public function testViolationsException(): void
    {
        $violationList = new ConstraintViolationList([
            new ConstraintViolation('Error code 1', null, [], null, 'code', null),
            new ConstraintViolation('Error code 2', null, [], null, 'code', null),
            new ConstraintViolation('Error labels[en_US] 1', null, [], null, 'labels[en_US]', null),
            new ConstraintViolation('Error labels[fr_FR] 1', null, [], null, 'labels[fr_FR]', null),
            new ConstraintViolation('Error labels[fr_FR] 2', null, [], null, 'labels[fr_FR]', null),
        ]);

        $exception = new ViolationsException($violationList);

        $this->assertEquals($violationList, $exception->violations());
        $this->assertEquals([
            'code' => ['Error code 1', 'Error code 2'],
            'labels' => [
                'en_US' => ['Error labels[en_US] 1'],
                'fr_FR' => ['Error labels[fr_FR] 1', 'Error labels[fr_FR] 2'],
            ],
        ], $exception->normalize());
    }

    public function test_message_contains_violation_text_when_constraint_violation_list(): void
    {
        $violationList = new ConstraintViolationList([
            new ConstraintViolation('Something went wrong', null, [], null, 'field', null),
        ]);

        $exception = new ViolationsException($violationList);

        // The exception message should contain the violation text (from ConstraintViolationList::__toString())
        $this->assertStringContainsString('Something went wrong', $exception->getMessage());
    }

    public function test_message_is_not_empty(): void
    {
        $violationList = new ConstraintViolationList([
            new ConstraintViolation('Error message', null, [], null, '', null),
        ]);

        $exception = new ViolationsException($violationList);
        $this->assertNotEmpty($exception->getMessage());
    }
}

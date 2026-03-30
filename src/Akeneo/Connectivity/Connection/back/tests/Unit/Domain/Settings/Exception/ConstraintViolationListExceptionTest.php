<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Exception;

use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationListExceptionTest extends TestCase
{
    private ConstraintViolationListInterface|MockObject $constraintViolationList;
    private ConstraintViolationListException $sut;

    protected function setUp(): void
    {
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->sut = new ConstraintViolationListException($this->constraintViolationList);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConstraintViolationListException::class, $this->sut);
    }

    public function test_it_returns_the_constraint_violation_list(): void
    {
        $this->assertSame($this->constraintViolationList, $this->sut->getConstraintViolationList());
    }
}

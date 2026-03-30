<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExist;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ConnectionMustExistTest extends TestCase
{
    private ConnectionMustExist $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionMustExist();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionMustExist::class, $this->sut);
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_a_target(): void
    {
        $this->assertSame(ConnectionMustExist::PROPERTY_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_provides_a_tag_to_be_validated(): void
    {
        $this->assertSame('connection_must_exist', $this->sut->validatedBy());
    }
}

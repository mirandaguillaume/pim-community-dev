<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class CodeMustBeUniqueTest extends TestCase
{
    private CodeMustBeUnique $sut;

    protected function setUp(): void
    {
        $this->sut = new CodeMustBeUnique();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CodeMustBeUnique::class, $this->sut);
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_a_target(): void
    {
        $this->assertSame(CodeMustBeUnique::PROPERTY_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_provides_a_tag_to_be_validated(): void
    {
        $this->assertSame('connection_code_must_be_unique', $this->sut->validatedBy());
    }
}

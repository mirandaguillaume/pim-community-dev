<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\ImageMustExist;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ImageMustExistTest extends TestCase
{
    private ImageMustExist $sut;

    protected function setUp(): void
    {
        $this->sut = new ImageMustExist();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ImageMustExist::class, $this->sut);
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_a_target(): void
    {
        $this->assertSame(ImageMustExist::PROPERTY_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_provides_a_tag_to_be_validated(): void
    {
        $this->assertSame('connection_image_must_exist', $this->sut->validatedBy());
    }
}

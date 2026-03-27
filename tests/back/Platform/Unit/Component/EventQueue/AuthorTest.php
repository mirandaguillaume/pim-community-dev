<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorTest extends TestCase
{
    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(Author::class, Author::class, true));
    }

    public function test_it_does_create_an_author_from_user(): void
    {
        $user = $this->createMock(UserInterface::class);

        $user->method('getUserIdentifier')->willReturn('julia');
        $user->method('getFirstName')->willReturn('Julia');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('isApiUser')->willReturn(false);
        $sut = Author::fromUser($user);
        $this->assertEquals('julia', $sut->name());
        $this->assertEquals(Author::TYPE_UI, $sut->type());
    }

    public function test_it_does_create_an_api_author_from_user(): void
    {
        $user = $this->createMock(UserInterface::class);

        $user->method('getUserIdentifier')->willReturn('julia');
        $user->method('getFirstName')->willReturn('Julia');
        $user->method('getLastName')->willReturn('Doe');
        $user->method('isApiUser')->willReturn(true);
        $sut = Author::fromUser($user);
        $this->assertEquals('julia', $sut->name());
        $this->assertEquals(Author::TYPE_API, $sut->type());
    }

    public function test_it_does_create_an_author_from_name_and_type(): void
    {
        $sut = Author::fromNameAndType('julia', Author::TYPE_UI);
        $this->assertEquals('julia', $sut->name());
        $this->assertEquals(Author::TYPE_UI, $sut->type());
    }

    public function test_it_not_does_create_an_author_from_name_and_type_because_of_wrong_type(): void
    {
        $this->expectException('\InvalidArgumentException');
        Author::fromNameAndType('julia', 'not_my_type');
    }
}

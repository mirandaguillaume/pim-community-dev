<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CommentController;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentControllerTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private ObjectManager|MockObject $doctrine;
    private RemoverInterface|MockObject $commentRemover;
    private CommentController $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->doctrine = $this->createMock(ObjectManager::class);
        $this->commentRemover = $this->createMock(RemoverInterface::class);
        $this->sut = new CommentController(
            $this->tokenStorage,
            $this->doctrine,
            $this->commentRemover,
            'AppComment',
        );
    }

    public function test_it_redirects_when_the_request_is_not_an_xml_http_request(): void
    {
        $response = $this->sut->deleteAction(Request::create('/', 'DELETE'), 'a_comment_id');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function test_it_throws_not_found_when_the_comment_does_not_exist(): void
    {
        $this->doctrine->method('find')->with('AppComment', 'unknown_id')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->deleteAction($this->xmlHttpRequest(), 'unknown_id');
    }

    public function test_it_refuses_to_delete_a_comment_authored_by_someone_else(): void
    {
        $author = $this->createMock(UserInterface::class);
        $currentUser = $this->createMock(UserInterface::class);
        $this->doctrine->method('find')->willReturn($this->comment($author));
        $this->authenticateAs($currentUser);
        $this->commentRemover->expects($this->never())->method('remove');

        $this->expectException(AccessDeniedException::class);

        $this->sut->deleteAction($this->xmlHttpRequest(), 'a_comment_id');
    }

    public function test_it_deletes_a_comment_authored_by_the_current_user(): void
    {
        $author = $this->createMock(UserInterface::class);
        $comment = $this->comment($author);
        $this->doctrine->method('find')->willReturn($comment);
        $this->authenticateAs($author);
        $this->commentRemover->expects($this->once())->method('remove')->with($comment);

        $response = $this->sut->deleteAction($this->xmlHttpRequest(), 'a_comment_id');

        $this->assertSame(200, $response->getStatusCode());
    }

    private function comment(UserInterface $author): object
    {
        return new class ($author) {
            public function __construct(private readonly UserInterface $author) {}

            public function getAuthor(): UserInterface
            {
                return $this->author;
            }
        };
    }

    private function authenticateAs(UserInterface $user): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
    }

    private function xmlHttpRequest(): Request
    {
        return Request::create('/', 'DELETE', [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Infrastructure\Controller;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Pim\Structure\Bundle\Infrastructure\Controller\MassDeleteAttributeGroupsController;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassDeleteAttributeGroupsControllerTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private JobLauncherInterface|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private SecurityFacadeInterface|MockObject $securityFacade;
    private MassDeleteAttributeGroupsController $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->jobInstanceRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacadeInterface::class);
        $this->sut = new MassDeleteAttributeGroupsController(
            $this->tokenStorage,
            $this->jobLauncher,
            $this->jobInstanceRepository,
            $this->securityFacade,
        );
        $this->jobInstanceRepository->method('findOneByIdentifier')
            ->with('delete_attribute_groups')
            ->willReturn($this->createMock(JobInstance::class));
    }

    public function test_it_rejects_a_non_xml_http_request(): void
    {
        $this->givenTheAuthenticatedUser($this->user());

        $response = ($this->sut)(Request::create('/', 'POST'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_rejects_the_request_when_no_user_is_authenticated(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $response = ($this->sut)($this->xmlHttpRequest());

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_it_rejects_the_request_when_the_user_lacks_the_mass_delete_permission(): void
    {
        $this->givenTheAuthenticatedUser($this->user());
        $this->securityFacade->method('isGranted')
            ->with('pim_enrich_attributegroup_mass_delete')
            ->willReturn(false);

        $response = ($this->sut)($this->xmlHttpRequest());

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_it_launches_the_delete_attribute_groups_job(): void
    {
        $user = $this->user('julia');
        $this->givenTheAuthenticatedUser($user);
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->jobLauncher->expects($this->once())
            ->method('launch')
            ->with(
                $this->isInstanceOf(JobInstance::class),
                $user,
                [
                    'filters' => ['codes' => ['a_group']],
                    'replacement_attribute_group_code' => 'other',
                    'users_to_notify' => ['julia'],
                    'send_email' => true,
                ],
            );

        $response = ($this->sut)($this->xmlHttpRequest([
            'codes' => ['a_group'],
            'replacement_attribute_group' => 'other',
        ]));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    private function givenTheAuthenticatedUser(UserInterface $user): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
    }

    private function user(string $identifier = 'a_user'): UserInterface|MockObject
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn($identifier);

        return $user;
    }

    private function xmlHttpRequest(array $query = []): Request
    {
        return Request::create('/', 'POST', $query, [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
    }
}

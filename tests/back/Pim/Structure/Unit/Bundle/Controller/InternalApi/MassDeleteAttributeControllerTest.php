<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Pim\Structure\Bundle\Controller\InternalApi\MassDeleteAttributeController;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassDeleteAttributeControllerTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private JobLauncherInterface|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private SecurityFacadeInterface|MockObject $securityFacade;
    private MassDeleteAttributeController $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->jobInstanceRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacadeInterface::class);
        $this->sut = new MassDeleteAttributeController(
            $this->tokenStorage,
            $this->jobLauncher,
            $this->jobInstanceRepository,
            $this->securityFacade,
        );
        $this->jobInstanceRepository->method('findOneByIdentifier')
            ->with('delete_attributes')
            ->willReturn($this->createMock(JobInstance::class));
    }

    public function test_it_rejects_the_request_when_the_user_lacks_the_mass_delete_permission(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_attribute_mass_delete')->willReturn(false);

        $response = $this->sut->launchAction(Request::create('/', 'POST'));

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_it_rejects_the_request_when_no_user_is_authenticated(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $response = $this->sut->launchAction(Request::create('/', 'POST'));

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_it_rejects_an_invalid_json_body(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->givenTheAuthenticatedUser('julia');

        $response = $this->sut->launchAction(Request::create('/', 'POST', [], [], [], [], '{not json'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(
            \json_encode(['message' => 'Invalid json message received']),
            $response->getContent(),
        );
    }

    public function test_it_launches_the_delete_attributes_job(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $user = $this->givenTheAuthenticatedUser('julia');
        $this->jobLauncher->expects($this->once())
            ->method('launch')
            ->with(
                $this->isInstanceOf(JobInstance::class),
                $user,
                [
                    'filters' => ['codes' => ['sku']],
                    'users_to_notify' => ['julia'],
                    'send_email' => true,
                ],
            );

        $response = $this->sut->launchAction(
            Request::create('/', 'POST', [], [], [], [], \json_encode(['filters' => ['codes' => ['sku']]])),
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    private function givenTheAuthenticatedUser(string $identifier): UserInterface|MockObject
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn($identifier);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);

        return $user;
    }
}

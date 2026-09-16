<?php

declare(strict_types=1);

namespace Akeneo\Channel\Test\Unit\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\FindCategoryTrees;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Controller\InternalApi\ChannelController;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelControllerTest extends TestCase
{
    private ChannelRepositoryInterface|MockObject $channelRepository;
    private NormalizerInterface|MockObject $normalizer;
    private ObjectUpdaterInterface|MockObject $updater;
    private SaverInterface|MockObject $saver;
    private RemoverInterface|MockObject $remover;
    private SimpleFactoryInterface|MockObject $channelFactory;
    private ValidatorInterface|MockObject $validator;
    private SecurityFacadeInterface|MockObject $securityFacade;
    private ChannelController $sut;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->updater = $this->createMock(ObjectUpdaterInterface::class);
        $this->saver = $this->createMock(SaverInterface::class);
        $this->remover = $this->createMock(RemoverInterface::class);
        $this->channelFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacadeInterface::class);

        $this->sut = new ChannelController(
            $this->channelRepository,
            $this->normalizer,
            $this->updater,
            $this->saver,
            $this->remover,
            $this->channelFactory,
            $this->validator,
            $this->securityFacade,
            $this->createMock(FindCategoryTrees::class),
        );
    }

    public function test_post_throws_access_denied_when_the_create_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_channel_create')->willReturn(false);
        $this->channelFactory->expects($this->never())->method('create');

        $this->expectException(AccessDeniedException::class);

        $this->sut->postAction($this->xhrRequest('POST', '{}'));
    }

    public function test_post_redirects_a_non_xhr_request_without_creating_a_channel(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->channelFactory->expects($this->never())->method('create');
        $this->saver->expects($this->never())->method('save');

        $response = $this->sut->postAction(Request::create('/configuration/channel/rest', 'POST', [], [], [], [], '{}'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function test_post_returns_a_bad_request_when_the_body_is_not_valid_json(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->channelFactory->method('create')->willReturn($this->createMock(ChannelInterface::class));
        $this->updater->expects($this->never())->method('update');
        $this->saver->expects($this->never())->method('save');

        $response = $this->sut->postAction($this->xhrRequest('POST', '{"code": "mobile"'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(['message' => 'Invalid json message received'], $this->decode($response));
    }

    public function test_post_returns_the_violations_keyed_by_property_path_without_saving(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelFactory->method('create')->willReturn($channel);
        $this->updater->expects($this->once())->method('update')->with($channel, ['code' => 'mobile']);
        $this->validator->method('validate')->with($channel)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('This value should not be blank.', '', [], $channel, 'category', null),
            new ConstraintViolation('The currency "GBP" has to be activated.', '', [], $channel, 'currencies[0]', 'GBP'),
        ]));
        $this->saver->expects($this->never())->method('save');

        $response = $this->sut->postAction($this->xhrRequest('POST', '{"code": "mobile"}'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame([
            'category' => ['message' => 'This value should not be blank.'],
            'currencies[0]' => ['message' => 'The currency "GBP" has to be activated.'],
        ], $this->decode($response));
    }

    public function test_post_saves_the_new_channel_and_returns_it_normalized_with_all_its_locales(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $channel = $this->createMock(ChannelInterface::class);
        $data = ['code' => 'mobile', 'category_tree' => 'master', 'currencies' => ['EUR'], 'locales' => ['fr_FR']];
        $this->channelFactory->expects($this->once())->method('create')->willReturn($channel);
        $this->updater->expects($this->once())->method('update')->with($channel, $data);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([]));
        $this->saver->expects($this->once())->method('save')->with($channel);
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($channel, 'internal_api', ['filter_locales' => false])
            ->willReturn(['code' => 'mobile', 'labels' => ['en_US' => 'Mobile app']]);

        $response = $this->sut->postAction($this->xhrRequest('POST', \json_encode($data)));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['code' => 'mobile', 'labels' => ['en_US' => 'Mobile app']], $this->decode($response));
    }

    public function test_put_throws_access_denied_when_the_edit_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_channel_edit')->willReturn(false);
        $this->saver->expects($this->never())->method('save');

        $this->expectException(AccessDeniedException::class);

        $this->sut->putAction($this->xhrRequest('PUT', '{}'), 'ecommerce');
    }

    public function test_put_redirects_a_non_xhr_request(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->saver->expects($this->never())->method('save');

        $response = $this->sut->putAction(Request::create('/', 'PUT', [], [], [], [], '{}'), 'ecommerce');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_put_throws_not_found_for_an_unknown_channel(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->channelRepository->method('findOneBy')->with(['code' => 'unknown'])->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->putAction($this->xhrRequest('PUT', '{}'), 'unknown');
    }

    public function test_put_updates_and_saves_the_existing_channel(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelRepository->method('findOneBy')->with(['code' => 'ecommerce'])->willReturn($channel);
        $this->channelFactory->expects($this->never())->method('create');
        $this->updater->expects($this->once())->method('update')->with($channel, ['locales' => ['en_US']]);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([]));
        $this->saver->expects($this->once())->method('save')->with($channel);
        $this->normalizer->method('normalize')->willReturn(['code' => 'ecommerce']);

        $response = $this->sut->putAction($this->xhrRequest('PUT', '{"locales": ["en_US"]}'), 'ecommerce');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['code' => 'ecommerce'], $this->decode($response));
    }

    public function test_remove_throws_access_denied_when_the_remove_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_channel_remove')->willReturn(false);
        $this->remover->expects($this->never())->method('remove');

        $this->expectException(AccessDeniedException::class);

        $this->sut->removeAction($this->xhrRequest('DELETE'), 'mobile');
    }

    public function test_remove_redirects_a_non_xhr_request_without_removing(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->remover->expects($this->never())->method('remove');

        $response = $this->sut->removeAction(Request::create('/', 'DELETE'), 'mobile');

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_remove_throws_not_found_for_an_unknown_channel(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->channelRepository->method('findOneBy')->willReturn(null);
        $this->remover->expects($this->never())->method('remove');

        $this->expectException(NotFoundHttpException::class);

        $this->sut->removeAction($this->xhrRequest('DELETE'), 'unknown');
    }

    public function test_remove_returns_a_bad_request_with_the_reason_when_the_channel_cannot_be_removed(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelRepository->method('findOneBy')->willReturn($channel);
        $this->remover->method('remove')->with($channel)->willThrowException(
            new \LogicException('You can not delete the last channel.')
        );

        $response = $this->sut->removeAction($this->xhrRequest('DELETE'), 'ecommerce');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(['message' => 'You can not delete the last channel.'], $this->decode($response));
    }

    public function test_remove_deletes_the_channel_and_returns_no_content(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelRepository->method('findOneBy')->with(['code' => 'mobile'])->willReturn($channel);
        $this->remover->expects($this->once())->method('remove')->with($channel);

        $response = $this->sut->removeAction($this->xhrRequest('DELETE'), 'mobile');

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function test_get_normalizes_the_channel_with_filtered_locales_by_default(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelRepository->method('findOneBy')->with(['code' => 'ecommerce'])->willReturn($channel);
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($channel, 'internal_api', ['filter_locales' => true])
            ->willReturn(['code' => 'ecommerce']);

        $response = $this->sut->getAction(Request::create('/'), 'ecommerce');

        $this->assertSame(['code' => 'ecommerce'], $this->decode($response));
    }

    public function test_get_does_not_filter_locales_when_asked_not_to(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $this->channelRepository->method('findOneBy')->willReturn($channel);
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($channel, 'internal_api', ['filter_locales' => false])
            ->willReturn(['code' => 'ecommerce']);

        $this->sut->getAction(Request::create('/', 'GET', ['filter_locales' => 'false']), 'ecommerce');
    }

    public function test_get_throws_not_found_for_an_unknown_channel(): void
    {
        $this->channelRepository->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->getAction(Request::create('/'), 'unknown');
    }

    private function xhrRequest(string $method, ?string $content = null): Request
    {
        return Request::create('/', $method, [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], $content);
    }

    private function decode(Response $response): array
    {
        return \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}

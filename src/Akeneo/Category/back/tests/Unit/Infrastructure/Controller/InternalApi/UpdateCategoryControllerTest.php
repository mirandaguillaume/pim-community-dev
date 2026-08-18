<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface;
use Akeneo\Category\Application\Filter\CategoryEditAclFilter;
use Akeneo\Category\Application\Filter\CategoryEditUserIntentFilter;
use Akeneo\Category\Domain\Event\CategoryEditedEvent;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Controller\InternalApi\UpdateCategoryController;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryControllerTest extends TestCase
{
    private const EDIT_ACL = 'pim_enrich_product_category_edit';
    private const CATEGORY_ID = 42;

    private CommandBus|MockObject $commandBus;
    private SecurityFacade|MockObject $securityFacade;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private ConverterInterface|MockObject $internalApiToStandardConverter;
    private CategoryEditAclFilter|MockObject $categoryEditAclFilter;
    private StandardFormatToUserIntentsInterface|MockObject $standardFormatToUserIntents;
    private CategoryEditUserIntentFilter|MockObject $categoryUserIntentFilter;
    private GetCategoryInterface|MockObject $getCategory;
    private FindCategoryAdditionalPropertiesRegistry|MockObject $findCategoryAdditionalPropertiesRegistry;
    private UpdateCategoryController $sut;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->internalApiToStandardConverter = $this->createMock(ConverterInterface::class);
        $this->categoryEditAclFilter = $this->createMock(CategoryEditAclFilter::class);
        $this->standardFormatToUserIntents = $this->createMock(StandardFormatToUserIntentsInterface::class);
        $this->categoryUserIntentFilter = $this->createMock(CategoryEditUserIntentFilter::class);
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->findCategoryAdditionalPropertiesRegistry = $this->createMock(FindCategoryAdditionalPropertiesRegistry::class);

        $this->sut = new UpdateCategoryController(
            $this->commandBus,
            $this->securityFacade,
            $this->eventDispatcher,
            $this->internalApiToStandardConverter,
            $this->categoryEditAclFilter,
            $this->standardFormatToUserIntents,
            $this->categoryUserIntentFilter,
            $this->getCategory,
            $this->findCategoryAdditionalPropertiesRegistry,
        );
    }

    public function testItDeniesAccessWhenTheEditAclIsNotGranted(): void
    {
        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->with(self::EDIT_ACL)
            ->willReturn(false);

        $this->getCategory->expects($this->never())->method('byId');
        $this->commandBus->expects($this->never())->method('dispatch');
        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedException::class);

        ($this->sut)($this->buildRequest(['code' => 'socks']), self::CATEGORY_ID);
    }

    public function testItThrowsANotFoundExceptionWhenTheCategoryDoesNotExist(): void
    {
        $this->securityFacade->method('isGranted')->with(self::EDIT_ACL)->willReturn(true);
        $this->getCategory->expects($this->once())
            ->method('byId')
            ->with(self::CATEGORY_ID)
            ->willReturn(null);

        $this->findCategoryAdditionalPropertiesRegistry->expects($this->never())->method('forCategory');
        $this->commandBus->expects($this->never())->method('dispatch');
        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Category not found');

        ($this->sut)($this->buildRequest(['code' => 'socks']), self::CATEGORY_ID);
    }

    public function testItConvertsFiltersAndDispatchesThenReturnsTheReloadedCategory(): void
    {
        $rawCategory = $this->buildCategory(['en_US' => 'Socks']);
        $enrichedCategory = $this->buildCategory(['en_US' => 'Socks']);
        $reloadedRawCategory = $this->buildCategory(['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']);
        $reloadedEnrichedCategory = $this->buildCategory(['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']);

        $requestBody = ['code' => 'socks', 'labels' => ['fr_FR' => 'Chaussettes'], 'permissions' => ['view' => [1]]];
        $standardFormat = ['code' => 'socks', 'labels' => ['fr_FR' => 'Chaussettes'], 'permissions' => ['view' => [1]]];
        $aclFilteredFormat = ['code' => 'socks', 'labels' => ['fr_FR' => 'Chaussettes']];

        $keptUserIntent = new SetLabel('fr_FR', 'Chaussettes');
        $droppedUserIntent = new SetLabel('en_US', 'Socks');

        $this->securityFacade->method('isGranted')->with(self::EDIT_ACL)->willReturn(true);

        $byIdArguments = [];
        $this->getCategory->expects($this->exactly(2))
            ->method('byId')
            ->willReturnCallback(
                function (int $categoryId) use (&$byIdArguments, $rawCategory, $reloadedRawCategory): Category {
                    $byIdArguments[] = $categoryId;

                    return 1 === count($byIdArguments) ? $rawCategory : $reloadedRawCategory;
                },
            );

        $this->findCategoryAdditionalPropertiesRegistry->expects($this->exactly(2))
            ->method('forCategory')
            ->willReturnCallback(
                fn(Category $category): Category => match (true) {
                    $category === $rawCategory => $enrichedCategory,
                    $category === $reloadedRawCategory => $reloadedEnrichedCategory,
                    default => throw new \LogicException('Unexpected category given to the additional properties registry'),
                },
            );

        $this->internalApiToStandardConverter->expects($this->once())
            ->method('convert')
            ->with($requestBody)
            ->willReturn($standardFormat);
        $this->categoryEditAclFilter->expects($this->once())
            ->method('filterCollection')
            ->with($standardFormat)
            ->willReturn($aclFilteredFormat);
        $this->standardFormatToUserIntents->expects($this->once())
            ->method('convert')
            ->with($aclFilteredFormat)
            ->willReturn([$keptUserIntent, $droppedUserIntent]);
        $this->categoryUserIntentFilter->expects($this->once())
            ->method('filterCollection')
            ->with([$keptUserIntent, $droppedUserIntent])
            ->willReturn([$keptUserIntent]);

        $dispatchedCommand = null;
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (object $command) use (&$dispatchedCommand) {
                $dispatchedCommand = $command;

                return null;
            });

        $dispatchedEvent = null;
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (object $event) use (&$dispatchedEvent): object {
                $dispatchedEvent = $event;

                return $event;
            });

        $response = ($this->sut)($this->buildRequest($requestBody), self::CATEGORY_ID);

        $this->assertSame([self::CATEGORY_ID, self::CATEGORY_ID], $byIdArguments);

        $this->assertInstanceOf(UpsertCategoryCommand::class, $dispatchedCommand);
        $this->assertSame('socks', $dispatchedCommand->categoryCode());
        $this->assertSame([$keptUserIntent], $dispatchedCommand->userIntents());

        $this->assertInstanceOf(CategoryEditedEvent::class, $dispatchedEvent);
        $this->assertSame($enrichedCategory, $dispatchedEvent->getCategory());
        $this->assertSame([$keptUserIntent], $dispatchedEvent->getUserIntents());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            [
                'success' => true,
                'category' => [
                    'id' => self::CATEGORY_ID,
                    'parent' => null,
                    'root_id' => null,
                    'template_uuid' => null,
                    'properties' => [
                        'code' => 'socks',
                        'labels' => ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes'],
                    ],
                    'attributes' => null,
                    'permissions' => null,
                ],
            ],
            $this->decode($response),
        );
    }

    public function testItReturnsABadRequestWithNormalizedViolationsWhenTheCommandIsRejected(): void
    {
        $this->givenAGrantedRequestOnAnExistingCategory();

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new ViolationsException(new ConstraintViolationList([
                new ConstraintViolation(
                    'This label is too long.',
                    null,
                    [],
                    null,
                    'labels[en_US]',
                    'a very long label',
                    null,
                    'label_too_long',
                ),
            ])));

        $this->eventDispatcher->expects($this->never())->method('dispatch');
        // The category must not be reloaded nor normalized when the edit was rejected.
        $this->getCategory->expects($this->once())->method('byId');

        $response = ($this->sut)($this->buildRequest(['code' => 'socks']), self::CATEGORY_ID);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(
            [
                [
                    'error' => [
                        'code' => 'label_too_long',
                        'property' => 'labels[en_US]',
                        'message' => 'This label is too long.',
                    ],
                ],
            ],
            $this->decode($response),
        );
    }

    public function testItReturnsABadRequestWhenTheEditedEventRaisesViolations(): void
    {
        $this->givenAGrantedRequestOnAnExistingCategory();

        $this->commandBus->expects($this->once())->method('dispatch')->willReturn(null);
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new ViolationsException(new ConstraintViolationList([
                new ConstraintViolation(
                    'This value is not allowed.',
                    null,
                    [],
                    null,
                    'values[description]',
                    'nope',
                    null,
                    'invalid_value',
                ),
            ])));

        $this->getCategory->expects($this->once())->method('byId');

        $response = ($this->sut)($this->buildRequest(['code' => 'socks']), self::CATEGORY_ID);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(
            [
                [
                    'error' => [
                        'code' => 'invalid_value',
                        'property' => 'values[description]',
                        'message' => 'This value is not allowed.',
                    ],
                ],
            ],
            $this->decode($response),
        );
    }

    public function testItDoesNotCatchExceptionsOtherThanViolations(): void
    {
        $this->givenAGrantedRequestOnAnExistingCategory();

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new \RuntimeException('Database is down'));

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database is down');

        ($this->sut)($this->buildRequest(['code' => 'socks']), self::CATEGORY_ID);
    }

    /**
     * Wires the happy path up to the command dispatch: granted ACL, existing category, pass-through pipeline.
     */
    private function givenAGrantedRequestOnAnExistingCategory(): void
    {
        $category = $this->buildCategory(['en_US' => 'Socks']);

        $this->securityFacade->method('isGranted')->with(self::EDIT_ACL)->willReturn(true);
        $this->getCategory->method('byId')->willReturn($category);
        $this->findCategoryAdditionalPropertiesRegistry->method('forCategory')->willReturn($category);
        $this->internalApiToStandardConverter->method('convert')->willReturn(['code' => 'socks']);
        $this->categoryEditAclFilter->method('filterCollection')->willReturn(['code' => 'socks']);
        $this->standardFormatToUserIntents->method('convert')->willReturn([new SetLabel('en_US', 'Socks')]);
        $this->categoryUserIntentFilter->method('filterCollection')->willReturn([new SetLabel('en_US', 'Socks')]);
    }

    /**
     * @param array<string, string> $labels
     */
    private function buildCategory(array $labels): Category
    {
        return new Category(
            id: new CategoryId(self::CATEGORY_ID),
            code: new Code('socks'),
            templateUuid: null,
            labels: LabelCollection::fromArray($labels),
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function buildRequest(array $body): Request
    {
        return Request::create(
            '/rest/categories/' . self::CATEGORY_ID,
            'POST',
            content: json_encode($body, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<mixed>
     */
    private function decode(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}

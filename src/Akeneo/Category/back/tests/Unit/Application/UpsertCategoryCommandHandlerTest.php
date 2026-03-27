<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application;

use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use Akeneo\Category\Application\Storage\Save\CategorySaverProcessor;
use Akeneo\Category\Application\UpsertCategoryCommandHandler;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private GetCategoryInterface|MockObject $getCategory;
    private UserIntentApplierRegistry|MockObject $applierRegistry;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private CategorySaverProcessor|MockObject $saver;
    private FindCategoryAdditionalPropertiesRegistry|MockObject $findCategoryAdditionalPropertiesRegistry;
    private UpsertCategoryCommandHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->applierRegistry = $this->createMock(UserIntentApplierRegistry::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->saver = $this->createMock(CategorySaverProcessor::class);
        $this->findCategoryAdditionalPropertiesRegistry = $this->createMock(FindCategoryAdditionalPropertiesRegistry::class);
        $this->sut = new UpsertCategoryCommandHandler(
            $this->validator,
            $this->getCategory,
            $this->applierRegistry,
            $this->eventDispatcher,
            $this->saver,
            $this->findCategoryAdditionalPropertiesRegistry,
        );
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(UpsertCategoryCommandHandler::class, $this->sut);
    }

    public function testItCreatesAndSavesACategory(): void
    {
        $command = new UpsertCategoryCommand('code');
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->getCategory->expects($this->once())->method('byCode')->with('code')->willReturn(null);
        $this->findCategoryAdditionalPropertiesRegistry->expects($this->never())->method('forCategory');
        $this->saver->expects($this->never())->method('save');
        $this->expectException(\Exception::class);
        $this->sut->__invoke($command);
    }

    public function testItThrowsAnExceptionWhenCommandIsNotValid(): void
    {
        $command = new UpsertCategoryCommand('');
        $violations = new ConstraintViolationList([
            new ConstraintViolation('error', null, [], $command, null, null),
        ]);
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn($violations);
        $this->getCategory->expects($this->never())->method('byCode');
        $this->findCategoryAdditionalPropertiesRegistry->expects($this->never())->method('forCategory');
        $this->applierRegistry->expects($this->never())->method('getApplier');
        $this->saver->expects($this->never())->method('save');
        $this->eventDispatcher->expects($this->never())->method('dispatch');
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function testItThrowsAnExceptionWhenUpdaterThrowsAnException(): void
    {
        $userIntentApplier = $this->createMock(UserIntentApplier::class);

        $setLabelUserIntent = new SetLabel('en_US', 'The label');
        $command = new UpsertCategoryCommand('code', [$setLabelUserIntent]);
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null,
        );
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->getCategory->expects($this->once())->method('byCode')->with('code')->willReturn($category);
        $this->findCategoryAdditionalPropertiesRegistry->expects($this->once())->method('forCategory')->with($category)->willReturn($category);
        $this->applierRegistry->method('getApplier')->with($setLabelUserIntent)->willReturn($userIntentApplier);
        $userIntentApplier->method('apply')->with($setLabelUserIntent, $category)->willThrowException(new \InvalidArgumentException());
        $this->saver->expects($this->never())->method('save');
        $this->eventDispatcher->expects($this->never())->method('dispatch');
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }
}

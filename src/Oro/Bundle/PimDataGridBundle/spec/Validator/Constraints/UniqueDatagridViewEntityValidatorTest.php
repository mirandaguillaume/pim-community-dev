<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Validator\Constraints\UniqueDatagridViewEntity;
use Oro\Bundle\PimDataGridBundle\Validator\Constraints\UniqueDatagridViewEntityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueDatagridViewEntityValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private DatagridViewRepositoryInterface|MockObject $datagridViewRepository;
    private UniqueDatagridViewEntityValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->datagridViewRepository = $this->createMock(DatagridViewRepositoryInterface::class);
        $this->sut = new UniqueDatagridViewEntityValidator($this->datagridViewRepository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UniqueDatagridViewEntityValidator::class, $this->sut);
    }

    public function test_it_adds_violation_to_the_context_if_a_public_datagrid_view_already_exists_with_the_same_label(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $datagridView = $this->createMock(DatagridView::class);
        $datagridViewInDatabase = $this->createMock(DatagridView::class);

        $constraint = new UniqueDatagridViewEntity();
        $datagridView->method('getId')->willReturn(1);
        $datagridView->method('getType')->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->method('getLabel')->willReturn('The best public view');
        $datagridViewInDatabase->method('getId')->willReturn(2);
        $this->datagridViewRepository->method('findPublicDatagridViewByLabel')->with('The best public view')->willReturn($datagridViewInDatabase);
        $this->context->method('buildViolation')->with('pim_datagrid.column_configurator.label.unique_message')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('atPath')->with('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($datagridView, $constraint);
    }

    public function test_it_adds_violation_to_the_context_if_a_private_datagrid_view_already_exists_with_the_same_label_and_same_user(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $datagridView = $this->createMock(DatagridView::class);
        $datagridViewInDatabase = $this->createMock(DatagridView::class);

        $constraint = new UniqueDatagridViewEntity();
        $user = new User();
        $datagridView->method('getId')->willReturn(1);
        $datagridView->method('getType')->willReturn(DatagridView::TYPE_PRIVATE);
        $datagridView->method('getLabel')->willReturn('The best private view');
        $datagridView->method('getOwner')->willReturn($user);
        $datagridViewInDatabase->method('getId')->willReturn(2);
        $this->datagridViewRepository->method('findPrivateDatagridViewByLabel')->with('The best private view', $user)->willReturn($datagridViewInDatabase);
        $this->context->method('buildViolation')->with('pim_datagrid.column_configurator.label.unique_message')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('atPath')->with('label')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($datagridView, $constraint);
    }

    public function test_it_does_not_add_violation_to_the_context_if_no_public_datagrid_view_already_exists_with_the_same_label(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $datagridView = $this->createMock(DatagridView::class);

        $constraint = new UniqueDatagridViewEntity();
        $datagridView->method('getId')->willReturn(null);
        $datagridView->method('getType')->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->method('getLabel')->willReturn('The best public view');
        $this->datagridViewRepository->method('findPublicDatagridViewByLabel')->with('The best public view')->willReturn(null);
        $constraintViolationBuilder->expects($this->never())->method('addViolation');
        $this->sut->validate($datagridView, $constraint);
    }

    public function test_it_does_not_add_violation_to_the_context_if_no_private_datagrid_view_already_exists_with_the_same_label_and_same_user(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $datagridView = $this->createMock(DatagridView::class);

        $constraint = new UniqueDatagridViewEntity();
        $user = new User();
        $datagridView->method('getId')->willReturn(null);
        $datagridView->method('getType')->willReturn(DatagridView::TYPE_PRIVATE);
        $datagridView->method('getLabel')->willReturn('The best private view');
        $datagridView->method('getOwner')->willReturn($user);
        $this->datagridViewRepository->method('findPrivateDatagridViewByLabel')->with('The best private view', $user)->willReturn(null);
        $constraintViolationBuilder->expects($this->never())->method('addViolation');
        $this->sut->validate($datagridView, $constraint);
    }

    public function test_it_does_not_add_violation_to_the_context_if_i_update_a_datagrid_view(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $datagridView = $this->createMock(DatagridView::class);

        $constraint = new UniqueDatagridViewEntity();
        $user = new User();
        $datagridView->method('getId')->willReturn(null);
        $datagridView->method('getType')->willReturn(DatagridView::TYPE_PUBLIC);
        $datagridView->method('getLabel')->willReturn('The best view');
        $datagridView->method('getOwner')->willReturn($user);
        $this->datagridViewRepository->method('findPublicDatagridViewByLabel')->with('The best view')->willReturn($datagridView);
        $constraintViolationBuilder->expects($this->never())->method('addViolation');
        $this->sut->validate($datagridView, $constraint);
    }
}

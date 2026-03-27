<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Command;

use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Command\AddAttributeCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddAttributeCommandHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private GetAttribute|MockObject $getAttribute;
    private CategoryTemplateAttributeSaver|MockObject $categoryTemplateAttributeSaver;
    private AddAttributeCommandHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->getAttribute = $this->createMock(GetAttribute::class);
        $this->categoryTemplateAttributeSaver = $this->createMock(CategoryTemplateAttributeSaver::class);
        $this->sut = new AddAttributeCommandHandler(
            $this->validator,
            $this->getAttribute,
            $this->categoryTemplateAttributeSaver
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddAttributeCommandHandler::class, $this->sut);
    }

    public function test_it_creates_and_saves_an_attribute(): void
    {
        $command = AddAttributeCommand::create(
            code: 'attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'The attribute'
        );
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->with($templateUuid)->willReturn(AttributeCollection::fromArray([]));
        $this->categoryTemplateAttributeSaver->expects($this->once())->method('insert')->with($templateUuid, $this->isInstanceOf(AttributeCollection::class));
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_command_is_not_valid_on_not_blank_values(): void
    {
        $command = AddAttributeCommand::create(
            code: '',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: '',
            label: ''
        );
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('This value should not be blank.', null, [], $command, 'code', null),
            new ConstraintViolation('This value should not be blank.', null, [], $command, 'locale', null),
        ]));
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->never())->method('byTemplateUuid')->with($templateUuid);
        $this->categoryTemplateAttributeSaver->expects($this->never())->method('insert')->with($templateUuid, $this->isInstanceOf(AttributeCollection::class));
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_command_is_not_valid_on_too_long_values(): void
    {
        $command = AddAttributeCommand::create(
            code: 'attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    In consectetur magna at magna consequat lacinia. Ut dapibus nulla sit amet nibh mattis aliquet.
                    In nec arcu eros. Suspendisse potenti. Etiam sagittis, diam sed commodo vehicula, libero mi mollis est.'
        );
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('This value is too long. It should have 100 characters or less.', null, [], $command, 'code', null),
            new ConstraintViolation('This value is too long. It should have 255 characters or less.', null, [], $command, 'label', null),
        ]));
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->never())->method('byTemplateUuid')->with($templateUuid);
        $this->categoryTemplateAttributeSaver->expects($this->never())->method('insert')->with($templateUuid, $this->isInstanceOf(AttributeCollection::class));
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_throws_an_exception_when_command_is_not_valid_on_wrong_format_values(): void
    {
        $command = AddAttributeCommand::create(
            code: 'Attribute code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'The attribute'
        );
        $this->validator->expects($this->once())->method('validate')->with($command)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('Attribute code may contain only lowercase letters, numbers and underscores', null, [], $command, 'code', null),
        ]));
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->never())->method('byTemplateUuid')->with($templateUuid);
        $this->categoryTemplateAttributeSaver->expects($this->never())->method('insert')->with($templateUuid, $this->isInstanceOf(AttributeCollection::class));
        $this->expectException(ViolationsException::class);
        $this->sut->__invoke($command);
    }

    public function test_it_saves_attribute_with_is_required_false(): void
    {
        $command = AddAttributeCommand::create(
            code: 'my_attr',
            type: 'text',
            isScopable: false,
            isLocalizable: false,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'My attr'
        );
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList());
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->willReturn(AttributeCollection::fromArray([]));

        $this->categoryTemplateAttributeSaver->expects($this->once())->method('insert')
            ->with($templateUuid, $this->callback(function (AttributeCollection $collection) {
                $attrs = $collection->getAttributes();
                $this->assertCount(1, $attrs);
                $attr = $attrs[0];
                // Verify isRequired is false (kills FalseValue mutation)
                $this->assertFalse($attr->isRequired()->normalize());
                return true;
            }));

        $this->sut->__invoke($command);
    }

    public function test_it_creates_attribute_with_label(): void
    {
        $command = AddAttributeCommand::create(
            code: 'my_attr',
            type: 'text',
            isScopable: false,
            isLocalizable: false,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: 'My Attribute Label'
        );
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList());
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->willReturn(AttributeCollection::fromArray([]));

        $this->categoryTemplateAttributeSaver->expects($this->once())->method('insert')
            ->with($templateUuid, $this->callback(function (AttributeCollection $collection) {
                $attrs = $collection->getAttributes();
                $this->assertCount(1, $attrs);
                $attr = $attrs[0];
                $labels = $attr->getLabelCollection()->normalize();
                $this->assertArrayHasKey('en_US', $labels);
                $this->assertSame('My Attribute Label', $labels['en_US']);
                return true;
            }));

        $this->sut->__invoke($command);
    }

    public function test_it_creates_attribute_with_empty_label(): void
    {
        $command = AddAttributeCommand::create(
            code: 'my_attr',
            type: 'text',
            isScopable: false,
            isLocalizable: false,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: ''
        );
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList());
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->willReturn(AttributeCollection::fromArray([]));

        $this->categoryTemplateAttributeSaver->expects($this->once())->method('insert')
            ->with($templateUuid, $this->callback(function (AttributeCollection $collection) {
                $attrs = $collection->getAttributes();
                $this->assertCount(1, $attrs);
                $attr = $attrs[0];
                $labels = $attr->getLabelCollection()->normalize();
                // Empty label should yield empty label collection
                $this->assertSame([], $labels);
                return true;
            }));

        $this->sut->__invoke($command);
    }

    public function test_it_creates_attribute_with_null_label(): void
    {
        $command = AddAttributeCommand::create(
            code: 'my_attr',
            type: 'text',
            isScopable: false,
            isLocalizable: false,
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            locale: 'en_US',
            label: null
        );
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList());
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->willReturn(AttributeCollection::fromArray([]));

        $this->categoryTemplateAttributeSaver->expects($this->once())->method('insert')
            ->with($templateUuid, $this->callback(function (AttributeCollection $collection) {
                $attrs = $collection->getAttributes();
                $this->assertCount(1, $attrs);
                $attr = $attrs[0];
                $labels = $attr->getLabelCollection()->normalize();
                $this->assertSame([], $labels);
                return true;
            }));

        $this->sut->__invoke($command);
    }
}

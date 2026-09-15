<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\GroupType\Validation;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * The group type constraints of Structure validation/grouptype.yml, mirroring AttributeGroupValidationIntegration.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeValidationIntegration extends TestCase
{
    public function testAValidGroupTypeHasNoViolation(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update($groupType, ['code' => 'special', 'labels' => ['en_US' => 'Special']]);

        $this->assertCount(0, $this->validate($groupType));
    }

    public function testGroupTypeUniqueEntity(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update($groupType, ['code' => 'RELATED']);

        $this->assertSingleViolation($this->validate($groupType), 'This value is already used.', 'code');
    }

    public function testGroupTypeImmutableCode(): void
    {
        $groupType = $this->get('pim_catalog.repository.group_type')->findOneByIdentifier('RELATED');
        $this->assertInstanceOf(GroupTypeInterface::class, $groupType);
        $this->getUpdater()->update($groupType, ['code' => 'RELATED_RENAMED']);

        $this->assertSingleViolation($this->validate($groupType), 'This property cannot be changed.', 'code');
    }

    public function testGroupTypeCodeNotBlank(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update($groupType, []);

        $this->assertSingleViolation($this->validate($groupType), 'This value should not be blank.', 'code');
    }

    public function testGroupTypeCodeRegex(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update($groupType, ['code' => 'group-type']);

        $this->assertSingleViolation(
            $this->validate($groupType),
            'Group type code may contain only letters, numbers and underscores.',
            'code',
        );
    }

    public function testGroupTypeCodeLength(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update($groupType, ['code' => str_pad('longCode', 101, 'l')]);

        $this->assertSingleViolation(
            $this->validate($groupType),
            'This value is too long. It should have 100 characters or less.',
            'code',
        );
    }

    public function testGroupTypeTranslationsLength(): void
    {
        $groupType = $this->createGroupType();
        $this->getUpdater()->update(
            $groupType,
            [
                'code' => 'group_type',
                'labels' => ['en_US' => str_pad('long_label', 101, '_')],
            ],
        );

        $this->assertSingleViolation(
            $this->validate($groupType),
            'This value is too long. It should have 100 characters or less.',
            'translations[0].label',
        );
    }

    private function assertSingleViolation(
        ConstraintViolationListInterface $violations,
        string $message,
        string $propertyPath,
    ): void {
        $this->assertCount(1, $violations, (string) $violations);
        $this->assertSame($message, $violations->get(0)->getMessage());
        $this->assertSame($propertyPath, $violations->get(0)->getPropertyPath());
    }

    private function validate(GroupTypeInterface $groupType): ConstraintViolationListInterface
    {
        return $this->get('validator')->validate($groupType);
    }

    private function createGroupType(): GroupTypeInterface
    {
        return $this->get('pim_catalog.factory.group_type')->create();
    }

    private function getUpdater(): object
    {
        return $this->get('pim_catalog.updater.group_type');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

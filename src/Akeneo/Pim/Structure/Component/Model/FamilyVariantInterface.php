<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyVariantInterface extends TranslatableInterface
{
    /**
     * For now the events are a list of strings, but they can be converted to object when needed.
     */
    public const AXES_WERE_UPDATED_ON_LEVEL = 'AXES_WERE_UPDATED_ON_LEVEL';
    public const ATTRIBUTES_WERE_UPDATED_ON_LEVEL = 'ATTRIBUTES_WERE_UPDATED_ON_LEVEL';

    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(string $code): void;

    public function getCommonAttributes(): CommonAttributeCollection;

    /**
     *
     *
     * @throws \InvalidArgumentException
     */
    public function getVariantAttributeSet(int $level): ?VariantAttributeSetInterface;

    /**
     * This method is needed for the validation of the variant attribute sets.
     * It is performed from the family variant, with the option "traversable: true".
     */
    public function getVariantAttributeSets(): Collection;

    public function getAttributes(): Collection;

    public function getAxes(): Collection;

    public function addVariantAttributeSet(VariantAttributeSetInterface $variantAttributeSet): void;

    public function setFamily(FamilyInterface $family): void;

    public function getFamily(): ?FamilyInterface;

    public function getNumberOfLevel(): int;

    /**
     * Returns the variant attribute set level in which a given attribute is located.
     *
     *
     * @throws \InvalidArgumentException
     *
     */
    public function getLevelForAttributeCode(string $attributeCode): int;

    /**
     * Get available axes attribute types
     */
    public static function getAvailableAxesAttributeTypes(): array;

    /**
     * @param AttributeInterface[] $axes
     */
    public function updateAxesForLevel(int $level, array $axes): void;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function updateAttributesForLevel(int $level, array $attributes): void;

    /**
     * @return string[]
     */
    public function releaseEvents(): array;
}

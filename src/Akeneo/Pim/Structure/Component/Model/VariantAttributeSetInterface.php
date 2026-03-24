<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VariantAttributeSetInterface
{
    public function getId(): int;

    public function getAttributes(): Collection;

    public function hasAttribute(AttributeInterface $attribute): bool;

    public function addAttribute(AttributeInterface $attribute): void;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function setAttributes(array $attributes): void;

    public function getAxes(): Collection;

    /**
     * @param AttributeInterface[] $axes
     */
    public function setAxes(array $axes): void;

    public function getLevel(): int;

    public function setLevel(int $level): void;

    public function getAxesLabels(string $locale): array;
}

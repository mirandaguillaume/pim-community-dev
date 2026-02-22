<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_family_variant_attribute_set')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class VariantAttributeSet implements VariantAttributeSetInterface
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    /** @var Collection */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_variant_attribute_set_has_attributes')]
    #[ORM\JoinColumn(name: 'variant_attribute_set_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'attributes_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private \Doctrine\Common\Collections\Collection $attributes;

    /** @var Collection */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_variant_attribute_set_has_axes')]
    #[ORM\JoinColumn(name: 'variant_attribute_set_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'axes_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private \Doctrine\Common\Collections\Collection $axes;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $level = null;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->axes = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return $this->containsAttribute($this->attributes, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute): void
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = new ArrayCollection($attributes);

        foreach ($this->axes as $axis) {
            $this->addAttribute($axis);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): Collection
    {
        return $this->axes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAxes(array $axes): void
    {
        foreach ($this->axes as $axis) {
            if ($this->hasAttribute($axis)) {
                $this->attributes->removeElement($axis);
            }
        }

        $this->axes = new ArrayCollection($axes);

        foreach ($this->axes as $axis) {
            $this->addAttribute($axis);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getAxesLabels(string $localeCode): array
    {
        $labels = [];

        foreach ($this->axes as $axis) {
            $axis->setLocale($localeCode);
            $labels[] = $axis->getLabel();
        }

        return $labels;
    }

    private function containsAttribute(Collection $attributes, AttributeInterface $attribute): bool
    {
        return $attributes->exists(fn ($key, $element) => $element->getCode() === $attribute->getCode());
    }
}

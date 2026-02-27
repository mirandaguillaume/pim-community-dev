<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Family entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository::class)]
#[ORM\Table(name: 'pim_catalog_family')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Family implements FamilyInterface, \Stringable
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    protected $code;

    /** @var Collection */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class, inversedBy: 'families')]
    #[ORM\JoinTable(name: 'pim_catalog_family_attribute')]
    #[ORM\JoinColumn(name: 'family_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'attribute_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $attributes;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var Collection */
    #[ORM\OneToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface::class, mappedBy: 'foreignKey', cascade: ['persist', 'detach', 'remove'], orphanRemoval: true, indexBy: 'locale')]
    protected $translations;

    /** @var AttributeInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class)]
    #[ORM\JoinColumn(name: 'label_attribute_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $attributeAsLabel;

    /** @var AttributeInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeInterface::class)]
    #[ORM\JoinColumn(name: 'image_attribute_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $attributeAsImage;

    /** @var Collection */
    #[ORM\OneToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface::class, mappedBy: 'family', cascade: ['persist', 'detach', 'remove', 'refresh'], orphanRemoval: true)]
    protected $requirements;

    /** @var \DateTime */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $created;

    /** @var \DateTime */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $updated;

    /** @var Collection */
    #[ORM\OneToMany(targetEntity: \Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface::class, mappedBy: 'family')]
    protected $familyVariants;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->requirements = new ArrayCollection();
        $this->familyVariants = new ArrayCollection();
    }

    /**
     * Returns the label of the family
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get created datetime
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     *
     * @return Family
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     *
     * @return Family
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(AttributeInterface $attribute)
    {
        $this->attributes->removeElement($attribute);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodes()
    {
        $codes = [];
        foreach ($this->attributes as $attribute) {
            $codes[] = $attribute->getCode();
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupedAttributes()
    {
        $result = [];
        foreach ($this->attributes as $attribute) {
            $result[(string) $attribute->getGroup()][] = $attribute;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute)
    {
        return $this->hasAttributeCode($attribute->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeCode($attributeCode)
    {
        return in_array($attributeCode, $this->getAttributeCodes());
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel)
    {
        $this->attributeAsLabel = $attributeAsLabel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabel()
    {
        return $this->attributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAsImage(?AttributeInterface $attributeAsImage): FamilyInterface
    {
        $this->attributeAsImage = $attributeAsImage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsImage(): ?AttributeInterface
    {
        return $this->attributeAsImage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabelChoices()
    {
        return $this->attributes->filter(
            fn($attribute) => in_array(
                $attribute->getType(),
                [AttributeTypes::TEXT, AttributeTypes::IDENTIFIER]
            )
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null): ?FamilyTranslationInterface
    {
        $locale = $locale ?: $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if (\strtolower((string) $translation->getLocale()) === \strtolower($locale)) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->set($translation->getLocale(), $translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return FamilyTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $translated = $this->getTranslation() ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '[' . $this->getCode() . ']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeRequirement(AttributeRequirementInterface $requirement)
    {
        $requirementKey = $this->getAttributeRequirementKey($requirement);
        $requirements = $this->getAttributeRequirements();

        if (!isset($requirements[$requirementKey])) {
            $requirement->setFamily($this);
            $this->requirements->add($requirement);
        } else {
            $requirements[$requirementKey]->setRequired($requirement->isRequired());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttributeRequirement(AttributeRequirementInterface $requirement)
    {
        $this->requirements->removeElement($requirement);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeRequirements(array $requirements)
    {
        foreach ($requirements as $requirement) {
            $requirement->setFamily($this);
        }
        $this->requirements = new ArrayCollection($requirements);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRequirements()
    {
        $result = [];

        foreach ($this->requirements as $requirement) {
            $key = $this->getAttributeRequirementKey($requirement);
            $result[$key] = $requirement;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRequirementKey(AttributeRequirementInterface $requirement)
    {
        return sprintf(
            '%s_%s',
            $requirement->getAttributeCode(),
            $requirement->getChannelCode()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyVariants(): Collection
    {
        return $this->familyVariants;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamilyVariants(Collection $familyVariants): void
    {
        $this->familyVariants = $familyVariants;
    }

    /**
     * @param AttributeInterface[] $attributes
     * @return void
     */
    public function updateAttributes(array $attributes = []): void
    {
        Assert::allIsInstanceOf($attributes, AttributeInterface::class);

        $formerAttributeCodes = $this->getAttributeCodes();
        $newAttributeCodes = \array_map(
            fn(AttributeInterface $attribute): string => $attribute->getCode(),
            $attributes
        );

        sort($formerAttributeCodes);
        sort($newAttributeCodes);

        if ($formerAttributeCodes !== $newAttributeCodes) {
            $attributeCodesToRemove = array_diff($formerAttributeCodes, $newAttributeCodes);
            $attributeCodesToAdd = array_diff($newAttributeCodes, $formerAttributeCodes);

            if (\count($attributeCodesToRemove) > 0) {
                $familyVariants = $this->getFamilyVariants();
                /** @var FamilyVariant $familyVariant */
                foreach ($familyVariants as $familyVariant) {
                    // for every family variants, we want to inform subscribers that the levels of product variants
                    // and product models have been updated
                    $familyVariant->addEvent(FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL);
                }
            }

            // removes attributes only from former attributes list
            foreach ($this->getAttributes() as $attribute) {
                if (\in_array($attribute->getCode(), $attributeCodesToRemove)) {
                    $this->removeAttribute($attribute);
                }
            }

            // adds attributes only from new attributes list
            foreach ($attributes as $attribute) {
                if (\in_array($attribute->getCode(), $attributeCodesToAdd)) {
                    $this->addAttribute($attribute);
                }
            }
        }
    }
}

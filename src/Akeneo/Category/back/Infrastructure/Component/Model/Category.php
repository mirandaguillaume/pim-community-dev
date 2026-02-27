<?php

namespace Akeneo\Category\Infrastructure\Component\Model;

use Akeneo\Category\Infrastructure\Component\Classification\Model\Category as BaseCategory;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category class allowing to organize a flexible product class into trees.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: \Akeneo\Category\Infrastructure\Doctrine\ORM\Repository\CategoryRepository::class)]
#[ORM\Table(name: 'pim_catalog_category')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\Index(columns: ['lft'], name: 'left_idx')]
#[ORM\Index(columns: ['updated'], name: 'updated_idx')]
#[ORM\UniqueConstraint(name: 'pim_category_code_uc', columns: ['code'])]
class Category extends BaseCategory implements CategoryInterface, \Stringable
{
    /** @var Collection<int, ProductInterface> */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface::class, mappedBy: 'categories', fetch: 'EXTRA_LAZY')]
    protected Collection $products;

    /** @var Collection<int, ProductModelInterface> */
    protected Collection $productModels;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property.
     *
     * @var string
     */
    protected $locale;

    /** @var Collection<int, TranslationInterface> */
    #[ORM\OneToMany(targetEntity: \Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface::class, mappedBy: 'foreignKey', cascade: ['persist', 'detach'], orphanRemoval: true)]
    protected $translations;

    /** @var Collection<int, Channel> */
    #[ORM\OneToMany(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface::class, mappedBy: 'category')]
    protected $channels;

    /** @var \DateTimeInterface */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $created;

    #[Gedmo\Timestampable(on: 'change', field: ['parent'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updated;

    public function __construct()
    {
        parent::__construct();

        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    public function hasProducts()
    {
        return $this->products->count() !== 0;
    }

    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Get products count.
     *
     * @return int
     */
    public function getProductsCount()
    {
        return $this->products->count();
    }

    public function hasProductModels(): bool
    {
        return $this->productModels->count() !== 0;
    }

    public function getProductModels(): Collection
    {
        return $this->productModels;
    }

    /**
     * Get created date.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated(\DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale ? $this->reformatLocale($locale) : $locale;

        return $this;
    }

    public function getTranslation(?string $locale = null): ?CategoryTranslationInterface
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

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function getTranslationFQCN()
    {
        return CategoryTranslation::class;
    }

    public function getLabel(): string
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '[' . $this->getCode() . ']';
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return CategoryInterface
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the channels linked to the category.
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getReference()
    {
        return $this->code;
    }

    private function reformatLocale(string $locale): string
    {
        $parts = explode('_', $locale);

        return implode('_', [strtolower($parts[0]), strtoupper($parts[1])]);
    }
}

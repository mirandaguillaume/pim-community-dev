<?php

namespace Akeneo\Channel\Infrastructure\Component\Model;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Channel entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Channel\Infrastructure\Doctrine\Repository\ChannelRepository::class)]
#[ORM\Table(name: 'pim_catalog_channel')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Channel implements ChannelInterface, \Stringable
{
    /** @var int $id */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string $code */
    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    protected $code;

    /** @var CategoryInterface $category */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Category\Infrastructure\Component\Model\CategoryInterface::class, inversedBy: 'channels')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected $category;

    /** @var ArrayCollection $currencies */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_channel_currency')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'currency_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $currencies;

    /** @var ArrayCollection $locales */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface::class, inversedBy: 'channels', cascade: ['persist', 'detach'])]
    #[ORM\JoinTable(name: 'pim_catalog_channel_locale')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'locale_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $locales;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ChannelTranslation[] */
    #[ORM\OneToMany(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\ChannelTranslationInterface::class, mappedBy: 'foreignKey', cascade: ['persist', 'detach', 'remove'], orphanRemoval: true)]
    protected $translations;

    /** @var array $conversionUnits */
    #[ORM\Column(type: Types::ARRAY)]
    protected $conversionUnits = [];

    /** @var array|ChannelEvent[] */
    private array $events = [];

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->locales = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Channel
     */
    public function setId($id)
    {
        $this->id = $id;

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
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null)
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
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
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
        return ChannelTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory(CategoryInterface $category)
    {
        if ($this->category === null) {
            $this->category = $category;

            return $this;
        }

        if ($this->category->getCode() !== $category->getCode()) {
            $previousCategoryCode = $this->category->getCode();
            $this->category = $category;
            $this->addEvent(new ChannelCategoryHasBeenUpdated($this->code, $previousCategoryCode, $category->getCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencies(array $currencies)
    {
        foreach ($this->currencies as $currency) {
            if (!in_array($currency, $currencies)) {
                $this->removeCurrency($currency);
            }
        }

        foreach ($currencies as $currency) {
            $this->addCurrency($currency);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCurrency(CurrencyInterface $currency)
    {
        if (!$this->hasCurrency($currency)) {
            $this->currencies[] = $currency;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCurrency(CurrencyInterface $currency)
    {
        $this->currencies->removeElement($currency);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCodes()
    {
        return $this->locales->map(
            fn ($locale) => $locale->getCode()
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocales(array $locales)
    {
        foreach ($this->locales as $locale) {
            if (!in_array($locale, $locales)) {
                $this->removeLocale($locale);
            }
        }

        foreach ($locales as $locale) {
            $this->addLocale($locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addLocale(LocaleInterface $locale)
    {
        if (!$this->hasLocale($locale)) {
            $this->locales[] = $locale;
            $locale->addChannel($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLocale(LocaleInterface $locale)
    {
        if ($this->locales->removeElement($locale)) {
            $locale->removeChannel($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale(LocaleInterface $locale)
    {
        return $this->locales->contains($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCurrency(CurrencyInterface $currency)
    {
        return $this->currencies->contains($currency);
    }

    /**
     * {@inheritdoc}
     */
    public function setConversionUnits(array $conversionUnits)
    {
        $this->conversionUnits = $conversionUnits;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConversionUnits()
    {
        return $this->conversionUnits;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * @return array|ChannelEvent[]
     */
    public function popEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function addEvent($event): void
    {
        $this->events[] = $event;
    }
}

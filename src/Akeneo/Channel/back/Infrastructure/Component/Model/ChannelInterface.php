<?php

namespace Akeneo\Channel\Infrastructure\Component\Model;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Event\ChannelEvent;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Channel interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelInterface extends ReferableInterface, VersionableInterface, TranslatableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return ChannelInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @return ChannelInterface
     */
    public function setLabel($label);

    /**
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * @return ChannelInterface
     */
    public function setCategory(CategoryInterface $category);

    /**
     * @return ArrayCollection
     */
    public function getCurrencies();

    public function setCurrencies(array $currencies);

    /**
     * @return ChannelInterface
     */
    public function addCurrency(CurrencyInterface $currency);

    /**
     * @return ChannelInterface
     */
    public function removeCurrency(CurrencyInterface $currency);

    /**
     * @return boolean
     */
    public function hasCurrency(CurrencyInterface $currency);

    /**
     * @return ArrayCollection
     */
    public function getLocales();

    public function setLocales(array $locales);

    /**
     * @return ChannelInterface
     */
    public function addLocale(LocaleInterface $locale);

    /**
     * @return ChannelInterface
     */
    public function removeLocale(LocaleInterface $locale);

    /**
     * @return bool
     */
    public function hasLocale(LocaleInterface $locale);

    /**
     * @return ChannelInterface
     */
    public function setConversionUnits(array $conversionUnits);

    /**
     * @return array
     */
    public function getConversionUnits();

    /**
     * Get locale codes
     *
     * @return array
     */
    public function getLocaleCodes();

    /**
     * To string
     *
     * @return string
     */
    public function __toString();

    /**
     * @return ChannelEvent[]
     */
    public function popEvents(): array;
}

<?php

namespace Akeneo\Tool\Component\Localization\Factory;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;

/**
 * Translation factory for entity instanciation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationFactory
{
    /**
     * The entity translation class
     *
     * @var string
     */
    protected $translationClass;

    /**
     * Constructor
     *
     * @param string $translationClass
     * @param string $entityClass
     * @param string $field
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($translationClass, protected $entityClass, protected $field)
    {
        $refl = new \ReflectionClass($translationClass);
        if (!$refl->isSubClassOf(AbstractTranslation::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The translation class "%s" must extends "%s"',
                    $translationClass,
                    AbstractTranslation::class
                )
            );
        }

        $this->translationClass = $translationClass;
    }

    /**
     * Create the translation entity
     *
     * @param string $locale
     *
     * @return AbstractTranslation
     */
    public function createTranslation($locale)
    {
        $translation = new $this->translationClass();
        $translation->setLocale($locale);

        return $translation;
    }
}

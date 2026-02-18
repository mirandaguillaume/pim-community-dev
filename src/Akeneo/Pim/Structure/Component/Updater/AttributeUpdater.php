<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates an attribute.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepo;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /** @var array<string> */
    protected $ignoredFields = [];

    /**
     * @param array<string>                     $properties
     * @param array<string>                     $ignoredFields
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $registry,
        TranslatableUpdater $translatableUpdater,
        private readonly array $properties,
        array $ignoredFields
    ) {
        $this->attrGroupRepo = $attrGroupRepo;
        $this->localeRepository = $localeRepository;
        $this->registry = $registry;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->translatableUpdater = $translatableUpdater;
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * {@inheritdoc}
     */
    public function update($attribute, array $data, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attribute),
                AttributeInterface::class
            );
        }

        foreach ($data as $field => $value) {
            if ($this->isFieldIgnored($field)) {
                continue;
            }
            $this->validateDataType($field, $value);
            $this->setData($attribute, $field, $value);
        }

        return $this;
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, mixed $data)
    {
        if (in_array($field, ['labels', 'available_locales', 'allowed_extensions', 'guidelines'])) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array(
            $field,
            array_merge([
                'code',
                'type',
                'group',
                'unique',
                'useable_as_grid_filter',
                'metric_family',
                'default_metric_unit',
                'reference_data_name',
                'max_characters',
                'validation_rule',
                'validation_regexp',
                'wysiwyg_enabled',
                'number_min',
                'number_max',
                'decimals_allowed',
                'negative_allowed',
                'date_min',
                'date_max',
                'max_file_size',
                'minimum_input_length',
                'sort_order',
                'localizable',
                'scopable',
                'required',
            ], $this->properties)
        )) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } elseif ('table_configuration' === $field) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param string             $field
     *
     * @throws InvalidPropertyException
     * @throws UnknownPropertyException
     */
    protected function setData(AttributeInterface $attribute, $field, mixed $data)
    {
        switch ($field) {
            case 'type':
                $this->setType($attribute, $data);
                break;
            case 'labels':
                $this->translatableUpdater->update($attribute, $data);
                break;
            case 'group':
                $this->setGroup($attribute, $data);
                break;
            case 'available_locales':
                $this->setAvailableLocales($attribute, $field, $data);
                break;
            case 'date_min':
                $this->validateDateFormat('date_min', $data);
                $date = $this->getDate($data);
                $attribute->setDateMin($date);
                break;
            case 'date_max':
                $this->validateDateFormat('date_max', $data);
                $date = $this->getDate($data);
                $attribute->setDateMax($date);
                break;
            case 'allowed_extensions':
                $attribute->setAllowedExtensions(implode(',', $data));
                break;
            case 'guidelines':
                foreach ($data as $localeCode => $localeGuidelines) {
                    if (null === $localeGuidelines || '' === $localeGuidelines) {
                        $attribute->removeGuidelines($localeCode);
                    } else {
                        $attribute->addGuidelines($localeCode, $localeGuidelines);
                    }
                }
                break;
            case 'table_configuration':
                $attribute->setRawTableConfiguration($data);
                break;
            default:
                if (in_array($field, $this->properties)) {
                    $attribute->setProperty($field, $data);
                } else {
                    $this->setValue($attribute, $field, $data);
                }
        }
    }

    /**
     * @param string $code
     */
    protected function findAttributeGroup($code): ?\Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface
    {
        $attributeGroup = $this->attrGroupRepo->findOneByIdentifier($code);

        return $attributeGroup;
    }

    /**
     * @param string             $field
     *
     * @throws UnknownPropertyException
     */
    protected function setValue(AttributeInterface $attribute, $field, mixed $data)
    {
        try {
            $this->accessor->setValue($attribute, $field, $data);
        } catch (NoSuchPropertyException $e) {
            throw UnknownPropertyException::unknownProperty($field, $e);
        }
    }

    /**
     * @param string                                                   $field
     *
     * @throws UnknownPropertyException
     * @throws InvalidPropertyException
     */
    protected function setAvailableLocales(AttributeInterface $attribute, $field, array $availableLocaleCodes)
    {
        $locales = [];
        foreach ($availableLocaleCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'available_locales',
                    'locale code',
                    'The locale does not exist',
                    static::class,
                    $localeCode
                );
            }

            $locales[] = $locale;
        }

        $this->setValue($attribute, $field, $locales);
    }

    /**
     * @param string             $data
     * @throws InvalidPropertyException
     */
    protected function setGroup(AttributeInterface $attribute, $data)
    {
        $attributeGroup = $this->findAttributeGroup($data);
        if (null === $attributeGroup) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'group',
                'code',
                'The attribute group does not exist',
                static::class,
                $data
            );
        }

        $attribute->setGroup($attributeGroup);
    }

    /**
     *
     * @throws InvalidPropertyException
     */
    protected function setType(AttributeInterface $attribute, mixed $data)
    {
        if (('' === $data) || (null === $data)) {
            throw InvalidPropertyException::valueNotEmptyExpected('type', static::class);
        }

        try {
            $attributeType = $this->registry->get($data);
        } catch (\LogicException) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'type',
                'attribute type',
                'The attribute type does not exist',
                static::class,
                $data
            );
        }

        $attribute->setType($attributeType->getName());
        $attribute->setBackendType($attributeType->getBackendType());
        if (true === $attributeType->isUnique() || null === $attribute->isUnique()) {
            $attribute->setUnique($attributeType->isUnique());
        }
    }

    /**
     * Valid dates:
     * - "2015-12-31T00:00:00+01:00"
     * - "2015-12-31"
     *
     * Wrong dates:
     * - "2015/12/31"
     * - "2015-45-31"
     * - "not a date"
     *
     * @param string $field
     * @param string $data
     *
     * @throws InvalidPropertyException
     */
    protected function validateDateFormat($field, $data)
    {
        if (null === $data) {
            return;
        }

        try {
            new \DateTime($data);
        } catch (\Exception) {
            throw InvalidPropertyException::dateExpected($field, 'yyyy-mm-dd', static::class, $data);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
            throw InvalidPropertyException::dateExpected($field, 'yyyy-mm-dd', static::class, $data);
        }
    }

    /**
     * @param string $date
     */
    protected function getDate($date): ?\DateTime
    {
        if (null === $date) {
            return null;
        }

        return new \DateTime($date);
    }

    private function isFieldIgnored(string $field): bool
    {
        return in_array($field, $this->ignoredFields);
    }
}

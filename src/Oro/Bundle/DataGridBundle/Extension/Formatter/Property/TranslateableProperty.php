<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class TranslateableProperty extends FieldProperty
{
    final public const string DOMAIN_KEY = 'domain';
    final public const string LOCALE_KEY = 'locale';

    /** @var array */
    protected $excludeParams = [self::DOMAIN_KEY, self::LOCALE_KEY];

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getRawValue(ResultRecordInterface $record)
    {
        $value = parent::getRawValue($record);

        return $this->translator->trans($value, [], $this->getOr(self::DOMAIN_KEY), $this->getOr(self::LOCALE_KEY));
    }
}

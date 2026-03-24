<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Webmozart\Assert\Assert;

/**
 * Normalize a metric data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer extends AbstractValueDataNormalizer
{
    final public const string LABEL_SEPARATOR = '-';
    final public const string MULTIPLE_FIELDS_FORMAT = 'multiple_fields';
    final public const string SINGLE_FIELD_FORMAT = 'single_field';
    final public const string UNIT_LABEL = 'unit';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof MetricInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     *
     * @param MetricInterface $object
     */
    #[\Override]
    public function normalize($object, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $context = $this->resolveContext($context);
        $decimalsAllowed = !array_key_exists('decimals_allowed', $context) || true === $context['decimals_allowed'];

        if (self::MULTIPLE_FIELDS_FORMAT === $context['metric_format']) {
            $fieldKey = $this->getFieldName($object, $context);
            $unitFieldKey = sprintf('%s-unit', $fieldKey);

            $data = $this->getMetricData($object, false, $decimalsAllowed);
            $result = [
                $fieldKey     => $data,
                $unitFieldKey => '' === $data ? '' : $object->getUnit(),
            ];
        } else {
            $result = [
                $this->getFieldName($object, $context) => $this->getMetricData($object, true, $decimalsAllowed),
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function doNormalize($object, $format = null, array $context = [])
    {
        Assert::implementsInterface($object, MetricInterface::class);

        return $object->getData();
    }

    /**
     * Get the data stored in the metric
     *
     * @param bool            $withUnit
     * @param bool            $decimalsAllowed
     * @return string
     */
    public function getMetricData(MetricInterface $metric, $withUnit, $decimalsAllowed = true): string|float|int
    {
        $data = $metric->getData();
        if (null === $data || '' === $data || 0 === $data) {
            return '';
        }

        $isInt = (int) $data == $data;
        $value = ($decimalsAllowed && !$isInt) ? (float) $data : (int) $data;
        if ($withUnit) {
            return sprintf('%s %s', $value, $metric->getUnit());
        }
        return $value;
    }

    /**
     * Merge default format option with context
     *
     *
     * @return array
     */
    protected function resolveContext(array $context = []): array
    {
        $context = array_merge(['metric_format' => self::MULTIPLE_FIELDS_FORMAT], $context);

        if (!in_array($context['metric_format'], [self::MULTIPLE_FIELDS_FORMAT, self::SINGLE_FIELD_FORMAT])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Value "%s" of "metric_format" context value is not allowed '
                    . '(allowed values: "%s, %s"',
                    $context['metric_format'],
                    self::SINGLE_FIELD_FORMAT,
                    self::MULTIPLE_FIELDS_FORMAT
                )
            );
        }

        return $context;
    }
}

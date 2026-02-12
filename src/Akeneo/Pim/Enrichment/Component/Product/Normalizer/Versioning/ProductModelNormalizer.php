<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\EntityWithQuantifiedAssociations\QuantifiedAssociationsNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a product model entity into a flat array
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    /** @staticvar string */
    private const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    private const FIELD_FAMILY_VARIANT = 'family_variant';

    /** @staticvar string */
    private const FIELD_CODE = 'code';

    private const FIELD_PARENT = 'parent';

    /** @staticvar string */
    private const ITEM_SEPARATOR = ',';

    public function __construct(private readonly QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $context = $this->resolveContext($context);

        $results = [];
        $familyVariant = $object->getFamilyVariant();
        $results[self::FIELD_FAMILY_VARIANT] = null === $familyVariant ? null : $familyVariant->getCode();
        $results[self::FIELD_CODE] = $object->getCode();
        $results[self::FIELD_CATEGORY] = implode(self::ITEM_SEPARATOR, $object->getCategoryCodes());
        $results[self::FIELD_PARENT] = $this->normalizeParent($object->getParent());
        $results = array_merge($results, $this->normalizeAssociations($object->getAssociations()));
        $results = array_merge($results, $this->quantifiedAssociationsNormalizer->normalize($object, $format, $context));
        $results = array_replace($results, $this->normalizeValues($object, $format, $context));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && in_array($format, ['flat']);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalizes a product parent.
     *
     *
     */
    private function normalizeParent(ProductModelInterface $parent = null): string
    {
        return $parent ? $parent->getCode() : '';
    }

    /**
     * Normalize values
     *
     *
     */
    private function normalizeValues(ProductModelInterface $productModel, ?string $format = null, array $context = []): array
    {
        $values = $productModel->getValuesForVariation();

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValue = $this->normalizer->normalize($value, $format, $context);
            $normalizedValues = array_replace($normalizedValues, $normalizedValue);
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Merge default format option with context
     *
     *
     */
    private function resolveContext(array $context): array
    {
        return array_merge(
            [
                'scopeCode'     => null,
                'localeCodes'   => [],
                'metric_format' => 'multiple_fields',
                'filter_types'  => ['pim.transform.product_value.flat']
            ],
            $context
        );
    }

    /**
     * Normalize associations
     *
     * @param AssociationInterface[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $results = [];
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();

            $groups = [];
            foreach ($association->getGroups() as $group) {
                $groups[] = $group->getCode();
            }

            $products = [];
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier();
            }

            $productModels = [];
            foreach ($association->getProductModels() as $productModel) {
                $productModels[] = $productModel->getCode();
            }

            $results[$columnPrefix . '-groups'] = implode(',', $groups);
            $results[$columnPrefix . '-products'] = implode(',', $products);
            $results[$columnPrefix . '-product_models'] = implode(',', $productModels);
        }

        return $results;
    }
}

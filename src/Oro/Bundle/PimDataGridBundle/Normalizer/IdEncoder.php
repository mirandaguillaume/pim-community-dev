<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

/**
 * Id encoder to manipulate product and product model ids
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdEncoder
{
    final public const PRODUCT_TYPE = 'product';
    final public const PRODUCT_MODEL_TYPE = 'product_model';

    /**
     * Encode id and type to a type_id format.
     *
     *
     */
    public static function encode(string $type, int|string $id): string
    {
        return sprintf('%s_%s', $type, $id);
    }

    /**
     * Decode the type_id format into id and type values
     *
     *
     */
    public static function decode(string $encodedId): array
    {
        $type = 1 !== preg_match(sprintf('/^%s_/', self::PRODUCT_MODEL_TYPE), $encodedId) ?
            self::PRODUCT_TYPE :
            self::PRODUCT_MODEL_TYPE;

        return [
            'id'   => str_replace($type . '_', '', $encodedId),
            'type' => $type,
        ];
    }
}

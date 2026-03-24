<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * Invalid argument exceptions the updater can throw when performing an action.
 *
 * @author    Julien Janvier <julien.jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    final public const int EXPECTED_CODE = 100;
    final public const int BOOLEAN_EXPECTED_CODE = 101;
    final public const int FLOAT_EXPECTED_CODE = 102;
    final public const int INTEGER_EXPECTED_CODE = 103;
    final public const int NUMERIC_EXPECTED_CODE = 104;
    final public const int STRING_EXPECTED_CODE = 105;
    final public const int ARRAY_EXPECTED_CODE = 106;
    final public const int ARRAY_OF_ARRAYS_EXPECTED_CODE = 107;

    final public const int ARRAY_KEY_EXPECTED_CODE = 200;
    final public const int ARRAY_INVALID_KEY_CODE = 201;
    final public const int ARRAY_NUMERIC_KEY_EXPECTED_CODE = 202;
    final public const int ARRAY_STRING_KEY_EXPECTED_CODE = 203;
    final public const int ARRAY_STRING_VALUE_EXPECTED_CODE = 204;
    final public const int EMPTY_ARRAY_CODE = 205;

    final public const int VALID_ENTITY_CODE_EXPECTED_CODE = 300;
    final public const int LOCALE_AND_SCOPE_EXPECTED_CODE = 301;
    final public const int SCOPE_EXPECTED_CODE = 302;
    final public const int ASSOCIATION_FORMAT_EXPECTED_CODE = 303;

    /**
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(protected $className, $message = '', $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string $name
     * @param string $expected
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function expected(string $name, $expected, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects %s as data, "%s" given.',
                $name,
                $expected,
                $data
            ),
            self::EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a boolean as data, "%s" given.',
                $name,
                $data
            ),
            self::BOOLEAN_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function floatExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a float as data, "%s" given.',
                $name,
                $data
            ),
            self::FLOAT_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an integer as data, "%s" given.',
                $name,
                $data
            ),
            self::INTEGER_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a numeric as data, "%s" given.',
                $name,
                $data
            ),
            self::NUMERIC_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a string as data, "%s" given.',
                $name,
                $data
            ),
            self::STRING_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array as data, "%s" given.',
                $name,
                $data
            ),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayOfArraysExpected(string $name, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array of arrays as data, "%s" given.',
                $name,
                $data
            ),
            self::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected(string $name, $key, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey(string $name, $key, $because, $className, $data): self
    {
        $err = 'Attribute or field "%s" expects an array with valid data for the key "%s". %s, "%s" given.';

        return new self(
            $className,
            sprintf($err, $name, $key, $because, $data),
            self::ARRAY_INVALID_KEY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function validEntityCodeExpected(string $name, $key, $because, $className, $data): self
    {
        $err = 'Attribute or field "%s" expects a valid %s. %s, "%s" given.';

        return new self(
            $className,
            sprintf($err, $name, $key, $because, $data),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected(string $name, $key, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with numeric data for the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_NUMERIC_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected(string $name, $key, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with string data for the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_STRING_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringValueExpected(string $name, $key, $className, $data): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with a string value for the key "%s", '
                . '"%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_STRING_VALUE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     *
     * @return InvalidArgumentException
     */
    public static function emptyArray(string $name): self
    {
        return new self(
            null,
            sprintf('Attribute or field "%s" expects a non empty array.', $name),
            self::EMPTY_ARRAY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     *
     * @return InvalidArgumentException
     */
    public static function localeAndScopeExpected(string $name, $className): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a valid scope and locale.',
                $name
            ),
            self::LOCALE_AND_SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     *
     * @return InvalidArgumentException
     */
    public static function scopeExpected(string $name, $className): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a valid scope.',
                $name
            ),
            self::SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param array  $data
     *
     * @return InvalidArgumentException
     */
    public static function associationFormatExpected(string $name, $data): self
    {
        return new self(
            null,
            sprintf(
                'Attribute or field "%s" expects a valid association format as ["associationTypeCode1" => '
                . '["products" => ["sku1, "sku2"], "groups" => ["group1"]]]", "%s" given.',
                $name,
                print_r($data, true)
            ),
            self::ASSOCIATION_FORMAT_EXPECTED_CODE
        );
    }

    /**
     * @param string     $name
     * @param string     $className
     * @return InvalidArgumentException
     */
    public static function expectedFromPreviousException(\Exception $exception, string $name, $className): self
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects valid data, scope and locale. %s',
                $name,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}

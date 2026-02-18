<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * Exception thrown when performing an action on a property with an unexpected data type.
 * For example, when a scalar is provided instead of an array.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidPropertyTypeException extends PropertyException
{
    final public const EXPECTED_CODE = 100;

    final public const SCALAR_EXPECTED_CODE = 101;
    final public const BOOLEAN_EXPECTED_CODE = 102;
    final public const FLOAT_EXPECTED_CODE = 103;
    final public const INTEGER_EXPECTED_CODE = 104;
    final public const NUMERIC_EXPECTED_CODE = 105;
    final public const STRING_EXPECTED_CODE = 106;
    final public const DECIMAL_EXPECTED_CODE = 107;

    final public const ARRAY_EXPECTED_CODE = 200;
    final public const VALID_ARRAY_STRUCTURE_EXPECTED_CODE = 201;
    final public const ARRAY_OF_ARRAYS_EXPECTED_CODE = 202;
    final public const ARRAY_KEY_EXPECTED_CODE = 203;
    final public const ARRAY_OF_OBJECTS_EXPECTED_CODE = 204;

    /**
     * @param string          $propertyName
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        $propertyName,
        protected mixed $propertyValue,
        protected $className,
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->propertyName = $propertyName;
    }

    /**
     * Build an exception when the data is not a scalar value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not a scalar (array, object, null)
     *
     * @return InvalidPropertyTypeException
     */
    public static function scalarExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a scalar as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::SCALAR_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a boolean value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that not a boolean
     *
     * @return InvalidPropertyTypeException
     */
    public static function booleanExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a boolean as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::BOOLEAN_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a float value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that not a float
     *
     * @return InvalidPropertyTypeException
     */
    public static function floatExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a float as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::FLOAT_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a integer value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that not an integer
     *
     * @return InvalidPropertyTypeException
     */
    public static function integerExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects an integer as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::INTEGER_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a numeric value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not a numeric
     *
     * @return InvalidPropertyTypeException
     */
    public static function numericExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a numeric as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::NUMERIC_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a decimal value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not a numeric
     *
     * @return InvalidPropertyTypeException
     */
    public static function decimalExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a decimal as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $propertyValue),
            self::DECIMAL_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not a string value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed $propertyValue a value that is not a string
     *
     * @return InvalidPropertyTypeException
     */
    public static function stringExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects a string as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::STRING_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not an array value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not an array (scalar, object, null)
     *
     * @return InvalidPropertyTypeException
     */
    public static function arrayExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects an array as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, gettype($propertyValue)),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data inside the array does not have the structure expected.
     * It's a generic exception to use if a most specific exception about array does not exist.
     *
     * @param string $propertyName
     * @param string $because
     * @param string $className
     * @param array  $propertyValue an array with an invalid structure
     *
     * @return InvalidPropertyTypeException
     */
    public static function validArrayStructureExpected($propertyName, $because, $className, array $propertyValue)
    {
        $message = 'Property "%s" expects an array with valid data, %s.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $because),
            self::VALID_ARRAY_STRUCTURE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data are not an array of arrays.
     *
     * @param string $propertyName
     * @param string $className
     * @param array  $propertyValue an array that does not contain arrays
     *
     * @return InvalidPropertyTypeException
     */
    public static function arrayOfArraysExpected($propertyName, $className, array $propertyValue)
    {
        $message = 'Property "%s" expects an array of arrays as data.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName),
            self::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data are not an array of objects.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not an array or does not contain object
     *
     * @return InvalidPropertyTypeException
     */
    public static function arrayOfObjectsExpected($propertyName, $className, mixed $propertyValue)
    {
        $message = 'Property "%s" expects an array of objects as data.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName),
            self::ARRAY_OF_OBJECTS_EXPECTED_CODE
        );
    }

    public static function arrayOfStringsExpected($propertyName, $className, $propertyValue)
    {
        $message = 'Property "%s" expects an array of strings as data.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName),
            self::ARRAY_OF_OBJECTS_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is an array that does not contain an expected key.
     *
     * @param string $propertyName
     * @param string $key
     * @param string $className
     * @param array  $propertyValue an array that does not contain a specific key
     *
     * @return InvalidPropertyTypeException
     */
    public static function arrayKeyExpected($propertyName, $key, $className, array $propertyValue)
    {
        $message = 'Property "%s" expects an array with the key "%s".';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $key),
            self::ARRAY_KEY_EXPECTED_CODE
        );
    }

    /**
     * @return string
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * Exception thrown when performing an action on a PQB operators with an unexpected value type.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidOperatorException extends \LogicException
{
    final public const EXPECTED_CODE = 100;
    final public const SCALAR_EXPECTED_CODE = 101;
    final public const ARRAY_EXPECTED_CODE = 200;
    final public const NOT_SUPPORTED_CODE = 300;
    final public const NOT_EMPTY_VALUE_EXPECTED_CODE = 400;

    /** @var array */
    protected $operators;

    /**
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        array $operators,
        protected mixed $value,
        protected $className,
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->operators = $operators;
    }

    /**
     * Build an exception when the operator receive a value which is not a scalar
     *
     * @param string $className
     * @param mixed  $value a value that is not a scalar (array, object, null)
     * @return InvalidOperatorException
     */
    public static function scalarExpected(array $operators, $className, mixed $value)
    {
        $message = 'Only scalar values are allowed for operators %s, "%s" given.';

        return new self(
            $operators,
            $value,
            $className,
            sprintf($message, implode(', ', $operators), gettype($value)),
            self::SCALAR_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the operator receive a value which is not an array
     *
     * @param array  $operators
     * @param string $className
     * @param mixed  $value a value that is not a scalar (array, object, null)
     *
     * @return InvalidOperatorException
     */
    public static function arrayExpected($operators, $className, mixed $value)
    {
        $message = 'Only array values are allowed for operators %s, "%s" given.';

        return new self(
            $operators,
            $value,
            $className,
            sprintf($message, implode(', ', $operators), gettype($value)),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the operator is not supported.
     *
     * @param string $operator
     * @param string $className
     *
     * @return InvalidOperatorException
     */
    public static function notSupported($operator, $className)
    {
        $message = 'Operator "%s" is not supported';

        return new self(
            [$operator],
            null,
            $className,
            sprintf($message, $operator),
            self::NOT_SUPPORTED_CODE
        );
    }

    /**
     * @return string
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}

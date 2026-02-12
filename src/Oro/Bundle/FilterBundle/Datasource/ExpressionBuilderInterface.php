<?php

namespace Oro\Bundle\FilterBundle\Datasource;

/**
 * Provides an interface for a data source restriction expressions generators
 */
interface ExpressionBuilderInterface
{
    /**
     * Creates a conjunction of the given boolean expressions.
     *
     * @param mixed $_ Expressions
     * @return mixed
     */
    public function andX(mixed $_);

    /**
     * Creates a disjunction of the given boolean expressions.
     *
     * @param mixed $_ Expressions
     * @return mixed
     */
    public function orX(mixed ...$_);

    /**
     * Creates an comparison expression with the given arguments.
     *
     * @param mixed  $x         Left expression
     * @param string $operator  Comparison operator
     * @param mixed  $y         Right expression
     * @param bool   $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function comparison(mixed $x, $operator, mixed $y, $withParam = false);

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function eq(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates an "!=" comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function neq(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates an "<" comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function lt(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates an "<=" comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function lte(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates an ">" comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function gt(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates an ">=" comparison expression with the given arguments.
     *
     * @param mixed $x         Left expression
     * @param mixed $y         Right expression
     * @param bool  $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function gte(mixed $x, mixed $y, $withParam = false);

    /**
     * Creates a negation expression of the given restriction.
     *
     * @param mixed $restriction Restriction to be used in NOT() function.
     * @return mixed
     */
    public function not(mixed $restriction);

    /**
     * Creates an IN() expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IN() function
     * @param mixed  $y Argument to be used in IN() function.
     * @param bool   $withParam Indicates whether the argument to be used in IN() function is a parameter name
     * @return mixed
     */
    public function in($x, mixed $y, $withParam = false);

    /**
     * Creates a NOT IN() expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by NOT IN() function
     * @param mixed  $y Argument to be used in NOT IN() function.
     * @param bool   $withParam Indicates whether the argument to be used in NOT IN() function is a parameter name
     * @return mixed
     */
    public function notIn($x, mixed $y, $withParam = false);

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IS NULL
     * @return mixed
     */
    public function isNull($x);

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IS NOT NULL
     * @return mixed
     */
    public function isNotNull($x);

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     *
     * @param string $x         Field in string format to be inspected by LIKE() comparison.
     * @param mixed  $y         Argument to be used in LIKE() comparison.
     * @param bool   $withParam Indicates whether the right expression is a parameter name
     * @return mixed
     */
    public function like($x, mixed $y, $withParam = false);
}

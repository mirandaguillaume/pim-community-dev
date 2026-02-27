<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * @param string $delimiter
     * @param string $filterUniqueValues
     */
    public function __construct(private $delimiter, private $filterUniqueValues)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value || [] === $value) {
            return '';
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return $this->transformArrayToString($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return $this->transformStringToArray($value);
    }

    /**
     * Transforms string to array
     *
     * @param string $stringValue
     * @return array
     */
    private function transformStringToArray($stringValue)
    {
        if (trim($this->delimiter)) {
            $separator = trim($this->delimiter);
        } else {
            $separator = $this->delimiter;
        }
        $arrayValue = explode($separator, $stringValue);
        return $this->filterArrayValue($arrayValue);
    }

    /**
     * Transforms array to string
     *
     * @return string
     */
    private function transformArrayToString(array $arrayValue)
    {
        if (trim($this->delimiter)) {
            $separator = trim($this->delimiter);
        } else {
            $separator = $this->delimiter;
        }
        return implode($separator, $this->filterArrayValue($arrayValue));
    }

    /**
     * Trims all elements and apply unique filter if needed
     *
     * @return array
     */
    private function filterArrayValue(array $arrayValue)
    {
        if ($this->filterUniqueValues) {
            $arrayValue = array_unique($arrayValue);
        }
        $arrayValue = array_filter(array_map('trim', $arrayValue));
        return array_values($arrayValue);
    }
}

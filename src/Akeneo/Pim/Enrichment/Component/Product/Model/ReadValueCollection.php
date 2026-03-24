<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Non indexed value collection for reading purpose
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReadValueCollection implements \Countable, \IteratorAggregate
{
    /** @var array|string[] */
    private array $attributeCodes = [];

    /**
     * @param ValueInterface[] $values
     */
    public function __construct(private array $values = [])
    {
        $attributeCodes = [];
        foreach ($this->values as $value) {
            $attributeCodes[] = $value->getAttributeCode();
        }
        $this->attributeCodes = array_unique($attributeCodes);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * {@inheritDoc}
     */
    public function first(): \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface|false
    {
        return reset($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function last(): \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface|false
    {
        return end($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return key($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface|false
    {
        return next($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface|false
    {
        return current($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function contains(ValueInterface $value): bool
    {
        return in_array($value, $this->values, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getValues(): array
    {
        return array_values($this->values);
    }

    public function getAttributeCodes(): array
    {
        return $this->attributeCodes;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function filter(\Closure $filterBy): self
    {
        $filteredValues = array_filter($this->values, $filterBy);

        return new self(array_values($filteredValues));
    }

    /**
     * {@inheritDoc}
     */
    public function map(\Closure $mapFunction): self
    {
        $transformedValues = array_map($mapFunction, $this->values);

        return new self(array_values($transformedValues));
    }
}

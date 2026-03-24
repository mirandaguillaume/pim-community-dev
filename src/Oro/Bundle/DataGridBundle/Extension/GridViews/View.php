<?php

namespace Oro\Bundle\DataGridBundle\Extension\GridViews;

class View
{
    protected array $filtersData;

    protected array $sortersData;

    /**
     * @param string $name
     */
    public function __construct(protected $name, array $filtersData = [], array $sortersData = [])
    {
        $this->filtersData = $filtersData;
        $this->sortersData = $sortersData;
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for sorters data
     *
     *
     * @return $this
     */
    public function setSortersData(array $sortersData): static
    {
        $this->sortersData = $sortersData;

        return $this;
    }

    /**
     * Getter for sorters data
     *
     * @return array
     */
    public function getSortersData()
    {
        return $this->sortersData;
    }

    /**
     * Setter for filter data
     *
     *
     * @return $this
     */
    public function setFiltersData(array $filtersData): static
    {
        $this->filtersData = $filtersData;

        return $this;
    }

    /**
     * Getter for filter data
     *
     * @return array
     */
    public function getFiltersData()
    {
        return $this->filtersData;
    }

    /**
     * Convert to view data
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name'    => $this->getName(),
            'filters' => $this->getFiltersData(),
            'sorters' => $this->getSortersData(),
        ];
    }
}

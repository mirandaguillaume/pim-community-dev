<?php

namespace Oro\Bundle\DataGridBundle\Extension\GridViews;

class View
{
    /** @var array */
    protected $filtersData;

    /** @var array */
    protected $sortersData;

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
    public function setSortersData(array $sortersData)
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
    public function setFiltersData(array $filtersData)
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
    public function getMetadata()
    {
        return [
            'name'    => $this->getName(),
            'filters' => $this->getFiltersData(),
            'sorters' => $this->getSortersData()
        ];
    }
}

<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

class SearchDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Opens the filter
     */
    public function open()
    {
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {
    }

    /**
     * Sets value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $field = $this->find('css', '[name="value"]');
        $field->setValue($value);
        $this->getSession()->executeScript(
            sprintf(
                '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="value"]\').trigger(\'change\')',
                $this->getAttribute('data-name'),
                $this->getAttribute('data-type')
            )
        );
    }

    /**
     * Return whether this filter input value is visible
     *
     * @return bool
     */
    public function isInputValueVisible()
    {
        try {
            $filterInput = $this->spin(function () {
                return $this->find('css', '[name="value"]');
            }, 'Cannot find the value input');
        } catch (TimeoutException $exception) {
            return false;
        }

        return $filterInput && $filterInput->isVisible();
    }

    /**
     * Search a value in the search filter.
     *
     * The search input is rendered with readonly="true" to work around a Chrome
     * autocomplete bug (see search-filter.js). The readonly is removed on focusin
     * and restored on focusout. W3C WebDriver refuses to clear() a readonly input,
     * so we must give focus first (which triggers disableReadonly), then set the value.
     *
     * @param string $value
     */
    public function search($value)
    {
        $this->getSession()->executeScript(
            sprintf(
                'var input = document.querySelector(\'.search-filter input[name="value"]\');' .
                'if (input) { input.removeAttribute("readonly"); input.focus(); }',
                $value
            )
        );
        $this->setValue($value);
    }
}

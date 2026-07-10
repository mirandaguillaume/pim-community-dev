<?php

namespace Pim\Behat\Decorator\Field;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MultiSelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Set the given value to the multi select (comma-separated for multi-value).
     *
     * Supports both the React DSM widget (Vague B: `.filter-select[data-testid="select-filter-widget"]`
     * opening a `document.body` overlay whose options are `[data-testid="<value>"]`) and the legacy
     * `jquery.multiselect` widget (`.select-filter-widget` → `li label:contains`).
     *
     * @throws \Exception
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $values = '' !== $value ? explode(',', $value) : [];

        if ($this->isReactWidget()) {
            $this->setReactValue($values);

            return;
        }

        $this->setLegacyValue($values, $value);
    }

    /**
     * @return bool
     */
    private function isReactWidget()
    {
        return null !== $this->find('css', '[data-testid="select-filter-widget"]');
    }

    /**
     * @param string[] $values
     */
    private function setReactValue(array $values)
    {
        foreach ($values as $value) {
            $value = trim($value);

            // Open the overlay (portaled to <body>) then click the option by its stable data-testid.
            $widget = $this->spin(function () {
                return $this->find('css', '[data-testid="select-filter-widget"]');
            }, 'Cannot find the React select widget');
            $widget->click();

            $option = $this->spin(function () use ($value) {
                return $this->getBody()->find('css', sprintf('[data-testid="%s"]', $value));
            }, sprintf('Cannot find option "%s"', $value));
            $option->click();
        }
    }

    /**
     * @param string[] $values
     * @param string   $rawValue
     *
     * @throws \Exception
     */
    private function setLegacyValue(array $values, $rawValue)
    {
        // The multiselect plugin can put many widgets in the DOM.
        // We have to find the one that is visible and active.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.select-filter-widget');
        }, sprintf('Could not find any multiselect widget for filter "%s"', $rawValue));

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception(
                sprintf('Could not find the multiselect widget for filter "%s"', $rawValue)
            );
        }
        $widget = end($visibleWidgets);

        // The search input for a multiselect is optional
        $search = $widget->find('css', 'input[type="search"]');
        foreach ($values as $value) {
            $value = trim($value);
            if (null !== $search) {
                $search->setValue($value);
            }

            $option = $this->spin(function () use ($widget, $value) {
                return $widget->find('css', sprintf('li label:contains("%s")', $value));
            }, sprintf('Cannot find option "%s"', $value));
            $option->click();
        }
    }
}

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
     * whose inner `<input>` opens a `#input-overlay-root` overlay portaled under `<body>`, options
     * matched by their visible label) and the legacy `jquery.multiselect` widget
     * (`.select-filter-widget` → `li label:contains`).
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

            // The overlay (portaled to <body>) only opens from the inner text input's click handler
            // (DSM SelectInput/MultiSelectInput wire `openOverlay` on the SearchInput/ChipInput `<input>`,
            // not on the outer `.filter-select` wrapper), so click that input rather than the wrapper.
            $input = $this->spin(function () {
                $widget = $this->find('css', '[data-testid="select-filter-widget"]');

                return null !== $widget ? $widget->find('css', 'input') : null;
            }, 'Cannot find the React select widget input');
            $input->click();

            // The DSM overlay is portaled directly under <body> as `#input-overlay-root`, so options are
            // NOT DOM descendants/siblings of the `.filter-select` wrapper; scope the lookup there.
            // Options are stamped `data-testid={value}` (the code) but their visible text is the label,
            // and Behat scenarios drive filters by label, so match on text, not on data-testid=value.
            $option = $this->spin(function () use ($value) {
                foreach ($this->getBody()->findAll('css', '#input-overlay-root [data-testid]') as $candidate) {
                    if ('backdrop' === $candidate->getAttribute('data-testid')) {
                        continue;
                    }
                    if (trim($candidate->getText()) === $value) {
                        return $candidate;
                    }
                }

                return null;
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

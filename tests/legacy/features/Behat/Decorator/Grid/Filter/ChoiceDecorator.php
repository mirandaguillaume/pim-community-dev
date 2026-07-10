<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\MultiSelectDecorator;

class ChoiceDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $field = $this->spin(function () {
            return $this->find('css', '.filter-select');
        }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

        $field = $this->decorate($field, [MultiSelectDecorator::class]);
        $field->setValue($value);

        $this->close();
    }

    /**
     * Get all available values in this filter (React DSM overlay or legacy jquery.multiselect menu).
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAvailableValues()
    {
        // React DSM: open the widget, read the option <span> texts from the <body> portal.
        $reactWidget = $this->find('css', '[data-testid="select-filter-widget"]');
        if (null !== $reactWidget) {
            $reactWidget->click();
            $options = $this->spin(function () {
                return $this->getBody()->findAll('css', '[data-testid]');
            }, 'Cannot find options');

            $values = [];
            foreach ($options as $option) {
                // Skip the widget wrapper itself; keep the option spans.
                if ('select-filter-widget' !== $option->getAttribute('data-testid')) {
                    $values[] = $option->getText();
                }
            }

            return array_filter($values);
        }

        // Legacy: find the visible/active multiselect menu, read its `li span` texts.
        $multiSelectWidgets = $this->spin(function () {
            return $this->getBody()->findAll('css', '.ui-multiselect-menu.select-filter-widget');
        }, 'Could not find any multiselect widget');

        $visibleWidgets = array_filter($multiSelectWidgets, function ($widget) {
            return $widget->isVisible();
        });

        if (empty($visibleWidgets)) {
            throw new \Exception('Could not find the multiselect widget');
        }
        $widget = end($visibleWidgets);

        $options = $this->spin(function () use ($widget) {
            return $widget->findAll('css', 'li span');
        }, 'Cannot find options');

        $values = [];
        foreach ($options as $option) {
            $values[] = $option->getText();
        }

        return array_filter($values);
    }

    /**
     * Closes the current choice filter
     */
    public function close()
    {
        $this->getSession()->executeScript("$(document)[0].dispatchEvent(new Event('mousedown'))");
    }
}

<?php

namespace Pim\Behat\Decorator\Field;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\SpinException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator for the React/DSM ViewSelectorCombobox (C1 Slice C).
 *
 * Replaces Select2Decorator for the product-grid view selector after the Select2→React swap.
 * The combobox root carries `.select2-container` (GridCapableDecorator anchor);
 * each option is wrapped in `.select2-result-label` (getAvailableValues/setValue contract).
 * The DSM overlay portal lives at `#input-overlay-root` in document.body.
 */
class ReactViewSelectorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Open the DSM SelectInput overlay by clicking the search input.
     */
    public function open(): void
    {
        $overlay = $this->getBody()->find('css', '#input-overlay-root');
        if (null !== $overlay) {
            return;
        }

        $input = $this->spin(function () {
            return $this->find('css', 'input[type="text"]');
        }, 'Could not find the combobox search input to open the overlay');

        $input->click();
    }

    /**
     * Close the DSM overlay by clicking the Backdrop portal element.
     */
    public function close(): void
    {
        $backdrop = $this->getBody()->find('css', '#input-overlay-root [data-testid="backdrop"]');
        if (null !== $backdrop) {
            $backdrop->click();
        }
    }

    /**
     * Return the DSM overlay root (#input-overlay-root) that contains the option list.
     */
    public function getWidget(): NodeElement
    {
        return $this->spin(function () {
            $this->open();

            $overlayRoot = $this->getBody()->find('css', '#input-overlay-root');
            if (null !== $overlayRoot && $overlayRoot->isVisible()) {
                return $overlayRoot;
            }

            return false;
        }, 'Could not find the React SelectInput overlay (#input-overlay-root)');
    }

    /**
     * Get the current view label from the DSM selected-option display.
     * ViewSelectorLine renders `.view-label` for the clean name (no dirty marker).
     */
    public function getCurrentValue(): string
    {
        $label = $this->spin(function () {
            return $this->find('css', '.view-label');
        }, 'Cannot find the current view label (.view-label)');

        return $label->getText();
    }

    /**
     * Return the available view labels visible in the overlay.
     * Options carry `.select2-result-label` (Approach A classname hook in ViewSelectorCombobox).
     *
     * @return array<string>
     */
    public function getAvailableValues(): array
    {
        $widget = $this->getWidget();

        $results = $this->spin(function () use ($widget) {
            $resultElements = $widget->findAll('css', '.select2-result-label, .select2-no-results');

            if (empty($resultElements)) {
                return ['results' => []];
            }

            if ($resultElements[0]->hasClass('select2-no-results')) {
                return ['results' => []];
            }

            $values = [];
            foreach ($resultElements as $element) {
                $values[] = $element->getText();
            }

            return ['results' => $values];
        }, 'Cannot find .select2-result-label or .select2-no-results in the overlay.');

        $this->spin(function () {
            $this->close();

            return true;
        }, 'Cannot close the view selector overlay');

        return $results['results'];
    }

    /**
     * Search for views by injecting a value into the combobox input and firing the input event.
     * jQuery `.val().trigger('input')` reaches DSM's React onChange handler.
     */
    public function search(string $text): void
    {
        $this->getWidget();

        $this->getSession()->executeScript(
            sprintf(
                '$(\'.grid-view-selector input[type="text"]\').val(\'%s\').trigger(\'input\');',
                addslashes($text)
            )
        );
    }

    /**
     * Select a view by its label — search then click the matching .select2-result-label.
     */
    public function setValue(string $value): void
    {
        $widget = $this->getWidget();
        $value = trim($value);

        $this->getSession()->executeScript(
            sprintf(
                '$(\'.grid-view-selector input[type="text"]\').val(\'%s\').trigger(\'input\');',
                addslashes($value)
            )
        );

        $this->spin(function () use ($widget, $value) {
            $result = $widget->find('css', sprintf('.select2-result-label:contains("%s")', $value));

            if (null !== $result && $result->isVisible()) {
                $result->click();

                return true;
            }

            throw new SpinException(sprintf('Could not find view "%s" in the overlay.', $value));
        }, sprintf('Could not click on view "%s" in the overlay.', $value));

        $this->spin(function () {
            $this->close();

            return true;
        }, 'Cannot close the view selector after selection');
    }
}

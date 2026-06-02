<?php

namespace Pim\Behat\Decorator\TabElement;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add comparison feature to an element
 */
class ComparisonPanelDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Change selection dropdown' => '.attribute-copy-actions .selection-dropdown *[data-toggle="dropdown"]',
        'Copy selected button'      => '.attribute-copy-actions .copy',
        'Copy source dropdown'      => '.attribute-copy-actions .source-switcher',
    ];

    /**
     * Change the current comparison selection given the specified mode ("all visible" or "all")
     *
     * @param string $mode
     */
    public function selectElements($mode)
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Change selection dropdown']) ?: false;
        }, 'Change selection dropdown was not found');

        // Open the menu only when it is not already open. Re-clicking the toggle while
        // the menu is open lets the decorative <li class="AknDropdown-menuTitle"> — which
        // overlaps the toggle during the 0.2s fadeIn animation — intercept the click,
        // the cause of the "element click intercepted" Spin timeout flaky on this step.
        $this->spin(function () use ($dropdown) {
            if (!$dropdown->getParent()->hasClass('open')) {
                $dropdown->click();
            }

            return $dropdown->getParent()->hasClass('open');
        }, 'Could not open the selection dropdown');

        // Click the requested option. This spin never touches the toggle again, so a
        // retry (e.g. while the count has not refreshed yet) cannot hit the intercept.
        // The menu links sit below the title, so they are never overlapped by it.
        $this->spin(function () use ($dropdown, $mode) {
            if (0 !== $this->selectedItemsCount()) {
                return true;
            }
            $selector = $dropdown->getParent()->find('css', sprintf('a:contains("%s")', ucfirst($mode)));
            if (null === $selector) {
                return false;
            }
            $selector->click();

            return 0 !== $this->selectedItemsCount();
        }, sprintf('Can not select "%s" elements', $mode));
    }

    /**
     * Click the link to copy selected translations
     */
    public function copySelectedElements()
    {
        $this->spin(function () {
            return 0 !== $this->selectedItemsCount();
        }, 'No selection before copy');

        $this->spin(function () {
            $copyButton = $this->find('css', $this->selectors['Copy selected button']);
            if (null === $copyButton) {
                return false;
            }
            $copyButton->click();

            return 0 === $this->selectedItemsCount();
        }, 'Still a selection after copy');
    }

    /**
     * @param string $source
     */
    public function switchSource($source)
    {
        $dropdown = $this->spin(function () {
            $dropdown = $this->find('css', $this->selectors['Copy source dropdown']);
            if (null === $dropdown) {
                return false;
            }

            return $dropdown;
        }, 'Copy source dropdown was not found');

        $toggle = $this->spin(function () use ($dropdown) {
            $toggle = $dropdown->find('css', '.AknActionButton');
            if (null === $toggle) {
                return false;
            }

            return $toggle;
        }, 'Dropdown action menu was not found');

        $this->spin(function () use ($toggle) {
            $toggle->click();

            return true;
        }, 'Could not click on dropdown menu');

        $option = $this->spin(function () use ($dropdown, $source) {
            $option = $dropdown->find('css', sprintf('.AknDropdown-menuLink[data-source="%s"]', $source));
            if (null === $option) {
                return false;
            }

            return $option;
        }, 'Dropdown link was not found');

        $option->click();
    }

    /**
     * Get le count of selected items in the panel
     *
     * @return integer
     */
    protected function selectedItemsCount()
    {
        $checkboxes = $this->spin(function () {
            return $this->getBody()->findAll('css', '.copy-field-selector');
        }, 'No checkbox found in copy panel');

        $checkedCount = 0;
        foreach ($checkboxes as $checkbox) {
            if ($checkbox->isChecked()) {
                $checkedCount++;
            }
        }

        return $checkedCount;
    }
}

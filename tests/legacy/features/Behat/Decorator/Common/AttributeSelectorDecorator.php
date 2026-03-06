<?php

namespace Pim\Behat\Decorator\Common;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorate the add attribute element
 */
class AttributeSelectorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Select the given attributes
     *
     * @param array $attributes
     */
    public function selectAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->spin(function () use ($attribute) {
                $headerInput = $this->find('css', 'header input');

                if (!$headerInput->isVisible() && $headerInput->isValid()) {
                    return false;
                }

                $headerInput->setValue($attribute);

                return true;
            }, 'Cannot fill the header input');

            $attributeItem = $this->spin(function () use ($attribute) {
                return $this->find('css', sprintf('li[data-attribute-code="%s"]', $attribute));
            }, sprintf('Cannot find the attribute %s in the list', $attribute));

            $dropZone = $this->spin(function () {
                return $this->find('css', '.selected-attributes ul');
            }, 'Cannot find the drop zone to select attributes');

            $this->dragElementTo($attributeItem, $dropZone);
        }
    }

    /**
     * Close the modal
     */
    public function close()
    {
        $button = $this->spin(function () {
            return $this->find('css', '.modal .ok');
        }, 'Cannot find the close button');

        $button->click();
    }

    /**
     * Clear the selected attributes
     */
    public function clear()
    {
        $button = $this->spin(function () {
            $button = $this->find('css', '.reset');
            if (null === $button) {
                $button = $this->find('css', '.clear');
            }

            return $button;
        }, 'Cannot find the clear button');

        $button->click();
        $this->spin(function () {
            $selectedAttributes = $this->find(
                'css',
                '.selected-attributes .AknColumnConfigurator-listContainer .AknVerticalList'
            );
            return false === $selectedAttributes->has('css', '.AknVerticalList-item');
        }, 'Cannot clear the selected attributes');
    }

    /**
     * Drags an element on another one.
     * Works better than the standard dragTo.
     *
     * @param $element
     * @param $dropZone
     */
    protected function dragElementTo($element, $dropZone)
    {
        $fromXpath = addcslashes($element->getXpath(), "'\\");
        $toXpath = addcslashes($dropZone->getXpath(), "'\\");

        $js = <<<JS
(function() {
    var from = document.evaluate('{$fromXpath}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    var to = document.evaluate('{$toXpath}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    if (!from || !to) return;

    var fromRect = from.getBoundingClientRect();
    var toRect = to.getBoundingClientRect();
    var fromX = fromRect.left + fromRect.width / 2;
    var fromY = fromRect.top + fromRect.height / 2;
    var toX = toRect.left + toRect.width / 2;
    var toY = toRect.top + toRect.height / 2;

    var opts = {bubbles: true, cancelable: true, view: window};

    from.dispatchEvent(new MouseEvent('mousedown', Object.assign({}, opts, {clientX: fromX, clientY: fromY})));
    from.dispatchEvent(new MouseEvent('mousemove', Object.assign({}, opts, {clientX: fromX, clientY: fromY})));
    to.dispatchEvent(new MouseEvent('mousemove', Object.assign({}, opts, {clientX: toX, clientY: toY})));
    to.dispatchEvent(new MouseEvent('mouseup', Object.assign({}, opts, {clientX: toX, clientY: toY})));
})();
JS;

        $this->getSession()->executeScript($js);
    }
}

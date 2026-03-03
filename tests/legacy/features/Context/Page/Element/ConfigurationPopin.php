<?php

namespace Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Datagrid configuration popin
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationPopin extends Element
{
    use SpinCapableTrait;

    protected $selector = ['css' => '.modal'];

    /**
     * @param string[] $labels
     */
    public function addColumns($labels)
    {
        $dropZone = $this->spin(function () {
            return $this->find('css', '#column-selection');
        }, 'Cannot find the drop zone to add columns');

        $this->loadAllColumns();

        foreach ($labels as $label) {
            $item = $this->getItemForLabel($label);
            $this->dragElementTo($item, $dropZone);
        }
    }

    /**
     * Run the infinite scroll on the column list
     */
    public function loadAllColumns()
    {
        return $this->spin(function () {
            $this->getSession()->executeScript('$("[data-columns]").scrollTop(10000);');

            return $this->find('css', '[data-columns].more') === null;
        }, 'Cannot load all columns in list');
    }

    /**
     * @param string[] $labels
     */
    public function removeColumns($labels)
    {
        $dropZone = $this->spin(function () {
            return $this->find('css', '#column-list');
        }, 'Cannot find the drop zone to remove columns');

        foreach ($labels as $label) {
            $item = $this->getItemForLabel($label);
            $this->dragElementTo($item, $dropZone);
        }
    }

    /**
     * Apply the configuration
     */
    public function apply()
    {
        $this->find('css', '.ok')->click();
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

    /**
     * @param string $label
     *
     * @throws TimeoutException
     *
     * @return NodeElement
     */
    protected function getItemForLabel($label)
    {
        return $this->spin(function () use ($label) {
            $items = $this->findAll('css', '.ui-sortable-handle');

            foreach ($items as $item) {
                if (strtolower($label) === strtolower($item->getText())) {
                    return $item;
                }
            }

            return false;
        }, sprintf('Cannot find the column "%s" in the list', $label));
    }
}

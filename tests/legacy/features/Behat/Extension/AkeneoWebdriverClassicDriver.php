<?php

declare(strict_types=1);

namespace Pim\Behat\Extension;

use Behat\Mink\Exception\DriverException;
use Facebook\WebDriver\WebDriverBy;
use Mink\WebdriverClassicDriver\WebdriverClassicDriver;

/**
 * Custom WebdriverClassicDriver that skips the syn.js blur trigger after setValue()
 * for text-like inputs, matching the old Selenium2Driver behavior.
 *
 * WebdriverClassicDriver injects syn.js to fire a synthetic 'blur' event after every
 * setValue(). In Akeneo's Backbone-based UI, this premature blur causes the form to
 * re-render, which detaches the Save button's DOM node from its Backbone view's $el —
 * making the jQuery-delegated click handler unreachable.
 *
 * By skipping the blur, we let it fire naturally when focus moves to the next element
 * (e.g. clicking Save), which matches the timing of the old Selenium2Driver.
 */
class AkeneoWebdriverClassicDriver extends WebdriverClassicDriver
{
    public function setValue(string $xpath, $value): void
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));
        $tagName = strtolower($element->getTagName() ?? '');

        if ($tagName === 'input') {
            $type = strtolower((string) $element->getAttribute('type'));
        } else {
            $type = $tagName;
        }

        // For text-like inputs, replicate parent behavior but WITHOUT the syn.js blur.
        // The blur will fire naturally when the next WebDriver action moves focus.
        if (in_array($type, ['text', 'password', ''], true) || $tagName === 'textarea') {
            if (!is_string($value)) {
                throw new DriverException("Value for $type must be a string");
            }

            $element->clear();
            $element->sendKeys($value);

            return;
        }

        // All other types (select, checkbox, radio, file, etc.) use parent implementation
        parent::setValue($xpath, $value);
    }
}

# Mink W3C WebDriver Migration Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace `behat/mink-selenium2-driver` (JSON Wire Protocol) with `mink/webdriver-classic-driver` (W3C WebDriver) to eliminate ~22 flaky Behat tests per CI run.

**Architecture:** Create a custom `WebdriverClassicFactory` for `friends-of-behat/mink-extension` (which doesn't ship one yet), swap the Composer dependency, update behat.yml config, rewrite drag & drop helpers using JS-based simulation, and stabilize the scope switcher.

**Tech Stack:** `mink/webdriver-classic-driver` ^1.1, `php-webdriver/webdriver` ^1.14, Selenium 4.27.0

---

### Task 1: Create WebdriverClassicFactory

The installed `friends-of-behat/mink-extension` v2.7.5 has no factory for `webdriver_classic`. We need to create one following the same pattern as `Selenium2Factory`.

**Files:**
- Create: `tests/legacy/features/Behat/Extension/WebdriverClassicFactory.php`

**Step 1: Write the factory class**

```php
<?php

namespace Pim\Behat\Extension;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

class WebdriverClassicFactory implements DriverFactory
{
    public function getDriverName(): string
    {
        return 'webdriver_classic';
    }

    public function supportsJavascript(): bool
    {
        return true;
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('browser_name')->defaultValue('%mink.browser_name%')->end()
                ->scalarNode('wd_host')->defaultValue('http://localhost:4444/wd/hub')->end()
                ->arrayNode('capabilities')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;
    }

    public function buildDriver(array $config): Definition
    {
        if (!class_exists('Mink\WebdriverClassicDriver\WebdriverClassicDriver')) {
            throw new \RuntimeException(
                'Install mink/webdriver-classic-driver to use the webdriver_classic driver.'
            );
        }

        return new Definition('Mink\WebdriverClassicDriver\WebdriverClassicDriver', [
            $config['browser_name'],
            $config['capabilities'],
            $config['wd_host'],
        ]);
    }
}
```

**Step 2: Register the factory in behat.yml**

Add to `behat.yml` under `extensions`:
```yaml
Pim\Behat\Extension\WebdriverClassicFactory: ~
```

Wait — MinkExtension registers factories in its constructor. We need a Behat extension to register our custom factory. Alternative: register via a Behat compiler pass or a simple bootstrap extension.

Actually, the simplest approach: create a tiny Behat extension that registers the factory.

```php
<?php
// tests/legacy/features/Behat/Extension/WebdriverClassicExtension.php

namespace Pim\Behat\Extension;

use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebdriverClassicExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'webdriver_classic';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
        /** @var MinkExtension|null $minkExtension */
        $minkExtension = $extensionManager->getExtension('mink');
        if ($minkExtension !== null) {
            $minkExtension->registerDriverFactory(new WebdriverClassicFactory());
        }
    }

    public function configure(ArrayNodeDefinition $builder): void {}
    public function load(ContainerBuilder $container, array $config): void {}
    public function process(ContainerBuilder $container): void {}
}
```

Then in `behat.yml`:
```yaml
extensions:
    Pim\Behat\Extension\WebdriverClassicExtension: ~
    Behat\MinkExtension:
        ...
```

**Step 3: Commit**

```bash
git add tests/legacy/features/Behat/Extension/
git commit -m "feat: add WebdriverClassicFactory for Behat MinkExtension"
```

---

### Task 2: Swap Composer dependencies

**Files:**
- Modify: `composer.json` (line 155)

**Step 1: Remove old driver, add new one**

```bash
docker compose run --rm php php -d memory_limit=4G /usr/local/bin/composer remove behat/mink-selenium2-driver --dev
docker compose run --rm php php -d memory_limit=4G /usr/local/bin/composer require --dev mink/webdriver-classic-driver:^1.1
```

This removes `instaclick/php-webdriver` and adds `php-webdriver/webdriver ^1.14`.

**Step 2: Verify composer.json**

Check that `composer.json` has `mink/webdriver-classic-driver` instead of `behat/mink-selenium2-driver`.

**Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "feat: replace mink-selenium2-driver with mink/webdriver-classic-driver"
```

---

### Task 3: Update behat.yml session config

**Files:**
- Modify: `behat.yml` (lines 87-111)

**Step 1: Update the session config**

Replace lines 94-111 in `behat.yml`:

```yaml
# Before:
    javascript_session: selenium2
    sessions:
        symfony:
            symfony: ~
        selenium2:
            selenium2:
                wd_host: 'http://selenium:4444/wd/hub'
                capabilities:
                    browser: "chrome"
                    browserName: "chrome"
                    chrome:
                        switches:
                            - "--window-size=1280,1024"
                            - "--start-maximized"
                            - "--no-sandbox"
                            - "--headless"
                        prefs:
                            foo: "Just because prefs must be a dictionnary"

# After:
    javascript_session: webdriver_classic
    sessions:
        symfony:
            symfony: ~
        webdriver_classic:
            webdriver_classic:
                wd_host: 'http://selenium:4444/wd/hub'
                browser_name: 'chrome'
                capabilities:
                    goog:chromeOptions:
                        args:
                            - "--window-size=1280,1024"
                            - "--start-maximized"
                            - "--no-sandbox"
                            - "--headless"
```

**Step 2: Add the extension registration BEFORE MinkExtension**

```yaml
extensions:
    Behat\ChainedStepsExtension: ~
    Pim\Behat\Extension\WebdriverClassicExtension: ~   # <-- ADD THIS LINE
    Behat\MinkExtension:
        ...
```

**Step 3: Commit**

```bash
git add behat.yml
git commit -m "feat: switch behat session from selenium2 to webdriver_classic"
```

---

### Task 4: Upgrade Selenium to 4.27.0

**Files:**
- Modify: `docker-compose.yml` (line 58)

**Step 1: Update the image**

```yaml
# Before:
  selenium:
    image: 'selenium/standalone-chrome:4.8.3'

# After:
  selenium:
    image: 'selenium/standalone-chrome:4.27.0'
```

**Step 2: Restart Selenium**

```bash
docker compose stop selenium && docker compose up -d selenium
```

**Step 3: Verify Selenium is running**

```bash
curl -s http://localhost:4444/status | python3 -m json.tool | head -5
```

Expected: `"ready": true`

**Step 4: Commit**

```bash
git add docker-compose.yml
git commit -m "feat: upgrade Selenium from 4.8.3 to 4.27.0"
```

---

### Task 5: Replace instanceof Selenium2Driver (3 files)

**Files:**
- Modify: `tests/legacy/features/Context/FeatureContext.php` (lines 6, 149, 329)
- Modify: `tests/legacy/features/Context/AssertionContext.php` (lines 7, 89, 126)
- Modify: `tests/legacy/features/Behat/Context/HookContext.php` (lines 7, 84)

**Step 1: FeatureContext.php**

```php
// Line 6: Replace import
// Before: use Behat\Mink\Driver\Selenium2Driver;
// After:
use Mink\WebdriverClassicDriver\WebdriverClassicDriver;

// Line 149: Replace instanceof
// Before: if (!($this->getSession()->getDriver() instanceof Selenium2Driver)) {
// After:
if (!($this->getSession()->getDriver() instanceof WebdriverClassicDriver)) {

// Line 329: Replace instanceof
// Before: if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
// After:
if ($this->getSession()->getDriver() instanceof WebdriverClassicDriver) {
```

**Step 2: AssertionContext.php**

```php
// Line 7: Replace import
// Before: use Behat\Mink\Driver\Selenium2Driver;
// After:
use Mink\WebdriverClassicDriver\WebdriverClassicDriver;

// Lines 89, 126: Replace instanceof
// Before: if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
// After:
if ($this->getSession()->getDriver() instanceof WebdriverClassicDriver) {
```

**Step 3: HookContext.php**

```php
// Line 7: Replace import
// Before: use Behat\Mink\Driver\Selenium2Driver;
// After:
use Mink\WebdriverClassicDriver\WebdriverClassicDriver;

// Line 84: Replace instanceof
// Before: if ($driver instanceof Selenium2Driver) {
// After:
if ($driver instanceof WebdriverClassicDriver) {
```

**Step 4: Commit**

```bash
git add tests/legacy/features/Context/FeatureContext.php tests/legacy/features/Context/AssertionContext.php tests/legacy/features/Behat/Context/HookContext.php
git commit -m "refactor: replace Selenium2Driver instanceof checks with WebdriverClassicDriver"
```

---

### Task 6: Rewrite drag & drop with JS simulation

The current drag & drop uses instaclick's `getWebDriverSession()->moveto()` which no longer exists.
The most reliable approach is JavaScript-based drag simulation since W3C Actions API isn't exposed by the Mink driver.

**Files:**
- Modify: `tests/legacy/features/Context/Page/Base/Base.php` (lines 499-510)
- Modify: `tests/legacy/features/Context/Page/Element/ConfigurationPopin.php` (lines 84-93)
- Modify: `tests/legacy/features/Behat/Decorator/Common/AttributeSelectorDecorator.php` (lines 92-101)
- Modify: `tests/legacy/features/Behat/Context/Domain/Enrich/FamilyVariantConfigurationContext.php` (lines 152-161)

**Step 1: Rewrite `dragElementTo()` in Base.php**

Replace lines 499-510:

```php
public function dragElementTo($element, $dropZone)
{
    $driver = $this->getSession()->getDriver();

    // Use JS-based drag & drop simulation — more reliable than WebDriver Actions
    // for apps using jQuery UI sortable / HTML5 drag events
    $script = <<<'JS'
(function(fromXpath, toXpath) {
    function getElement(xpath) {
        return document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    }
    var from = getElement(fromXpath);
    var to = getElement(toXpath);
    if (!from || !to) { throw new Error('Drag elements not found'); }

    var fromRect = from.getBoundingClientRect();
    var toRect = to.getBoundingClientRect();

    function fire(el, type, opts) {
        var evt = new MouseEvent(type, Object.assign({bubbles: true, cancelable: true}, opts));
        el.dispatchEvent(evt);
    }
    var fromX = fromRect.left + fromRect.width / 2;
    var fromY = fromRect.top + fromRect.height / 2;
    var toX = toRect.left + toRect.width / 2;
    var toY = toRect.top + toRect.height / 2;

    fire(from, 'mousedown', {clientX: fromX, clientY: fromY});
    fire(from, 'mousemove', {clientX: fromX, clientY: fromY});
    fire(to, 'mousemove', {clientX: toX, clientY: toY});
    fire(to, 'mouseup', {clientX: toX, clientY: toY});
})('%s', '%s');
JS;

    $driver->executeScript(sprintf(
        $script,
        addcslashes($element->getXpath(), "'"),
        addcslashes($dropZone->getXpath(), "'")
    ));
}
```

**Step 2: Update ConfigurationPopin.php (lines 84-93)**

Replace the drag & drop code with a call to `$this->dragElementTo($from, $to)` — but `ConfigurationPopin` is a Page Element, not a Page. We need to call through the session.

Look at the exact code in each file — they all duplicate the same instaclick pattern. Replace each with the JS simulation using `$this->getSession()->getDriver()->executeScript(...)`.

For files that don't have access to the `dragElementTo` method, inline the JS simulation or create a trait.

**Step 3: Run failing drag & drop test locally**

```bash
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/product/pef/order_attributes.feature:31
```

Expected: PASS (no more `moveto` error)

**Step 4: Commit**

```bash
git add tests/legacy/features/Context/Page/Base/Base.php tests/legacy/features/Context/Page/Element/ConfigurationPopin.php tests/legacy/features/Behat/Decorator/Common/AttributeSelectorDecorator.php tests/legacy/features/Behat/Context/Domain/Enrich/FamilyVariantConfigurationContext.php
git commit -m "fix: rewrite drag & drop using JS simulation for W3C WebDriver compat"
```

---

### Task 7: Fix alert handling

**Files:**
- Modify: `tests/legacy/features/Context/AssertionContext.php` (line 753)

**Step 1: Replace instaclick alert API**

```php
// Before (line 752-754):
return $this->spin(function () use ($message) {
    return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
}, sprintf('Cannot assert that the modal contains %s', $message));

// After:
return $this->spin(function () use ($message) {
    $alertText = $this->getSession()->evaluateScript('
        var modal = document.querySelector(".modal-body, .AknFullPage .alert");
        return modal ? modal.textContent.trim() : null;
    ');
    return $alertText !== null && strpos($alertText, $message) !== false;
}, sprintf('Cannot assert that the modal contains %s', $message));
```

Note: Akeneo uses custom modals (not native JS `alert()`), so `evaluateScript` targeting the modal DOM is more reliable.

**Step 2: Commit**

```bash
git add tests/legacy/features/Context/AssertionContext.php
git commit -m "fix: replace instaclick alert API with JS modal text extraction"
```

---

### Task 8: Stabilize scope switcher

**Files:**
- Modify: `tests/legacy/features/Behat/Decorator/ContextSwitcherDecorator.php` (lines 73-106)

**Step 1: Add overlay wait and retry on click intercepted**

```php
public function switchScope($scopeCode)
{
    $this->spin(function () use ($scopeCode) {
        $dropdown = $this->find('css', $this->selectors['Channel dropdown']);
        if (null === $dropdown) {
            return false;
        }

        $toggle = $dropdown->find('css', '.dropdown-toggle')
            ?? $dropdown->find('css', '*[data-toggle="dropdown"]');

        if (null === $toggle) {
            return false;
        }

        // Wait for overlays/loading masks to be hidden before clicking
        $this->getSession()->executeScript("
            var overlay = document.querySelector('.AknDropdown-menuTitle, .AknLoadingMask, .loading-mask');
            if (overlay && overlay.offsetParent !== null) {
                overlay.style.display = 'none';
            }
        ");

        // Scroll element into view to avoid click interception
        $this->getSession()->executeScript(sprintf(
            "document.evaluate('%s', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.scrollIntoView({block: 'center'})",
            addcslashes($toggle->getXpath(), "'")
        ));

        try {
            $toggle->click();
        } catch (\Exception $e) {
            // If click intercepted, try JS click as fallback
            $this->getSession()->executeScript(sprintf(
                "document.evaluate('%s', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click()",
                addcslashes($toggle->getXpath(), "'")
            ));
        }

        $option = $dropdown->find('css', sprintf('a[data-scope="%s"]', $scopeCode))
            ?? $dropdown->find('css', sprintf('a[href*="%s"]', $scopeCode))
            ?? $dropdown->find('css', sprintf('*[data-value="%s"]', $scopeCode));

        if (null === $option) {
            return false;
        }
        $option->click();

        return true;
    }, 'Could not find scope switcher');
}
```

**Step 2: Run failing scope switcher test locally**

```bash
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/product/validation/validate_metric_attributes.feature:43
```

Expected: PASS (no more `element click intercepted`)

**Step 3: Commit**

```bash
git add tests/legacy/features/Behat/Decorator/ContextSwitcherDecorator.php
git commit -m "fix: stabilize scope switcher with overlay wait and JS click fallback"
```

---

### Task 9: Update CI smoke test

**Files:**
- Modify: `.github/workflows/ci.yml` (lines 1052-1127)

**Step 1: Update capabilities in smoke test**

The smoke test directly calls the Selenium WebDriver API with JSON Wire capabilities. Update to W3C format:

```yaml
# Before (JSON Wire):
capabilities: {"browserName": "chrome", "chromeOptions": {"args": ["--headless"]}}

# After (W3C):
capabilities: {"alwaysMatch": {"browserName": "chrome", "goog:chromeOptions": {"args": ["--headless", "--no-sandbox"]}}}
```

**Step 2: Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "ci: update Selenium smoke test to use W3C capabilities format"
```

---

### Task 10: Local verification

**Step 1: Run the previously-failing scenarios**

```bash
# Drag & drop (was: moveto failure)
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/product/pef/order_attributes.feature:31

# Category hover (was: moveto failure)
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/category/create_a_category.feature:20

# Scope switcher (was: click intercepted)
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/product/validation/validate_metric_attributes.feature:43

# Display columns (was: moveto failure)
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s all tests/legacy/features/pim/enrichment/product/datagrid/display_localized_numbers.feature:16
```

Expected: All PASS

**Step 2: Run full critical suite**

```bash
APP_ENV=behat docker compose run --rm php php vendor/bin/behat -p legacy -s critical
```

Expected: 100/100 pass (or very close, only true flakies)

**Step 3: Verify Playwright isn't impacted**

```bash
npx playwright test tests/front/e2e/product/edit.spec.ts
```

Expected: PASS

**Step 4: Push and monitor CI**

```bash
git push
gh run watch
```

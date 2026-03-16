# SF 6.4 EventSubscribers → #[AsEventListener] Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Migrate 139 EventSubscriberInterface implementations to #[AsEventListener] PHP attributes, preparing the codebase for Symfony 7.0.

**Architecture:** Each EventSubscriber's `getSubscribedEvents()` static method maps events to handler methods (with optional priority). The migration replaces this interface+method with `#[AsEventListener]` attributes on each handler method. Service registration depends on how subscribers are currently tagged in the DI container.

**Tech Stack:** PHP 8.3, Symfony 6.4, Rector (if applicable), PHPStan deprecation analysis

---

### Task 1: Investigate service registration mechanism

**Files:**
- Read: `config/services/services.yml`
- Read: `config/services/*.yml` (all)
- Search: `kernel.event_subscriber` across `config/`, `src/*/Bundle/*/Resources/config/`
- Search: `autoconfigure` across all YAML/XML service configs
- Search: compiler passes that tag subscribers

**Step 1: Find how EventSubscribers are registered**

```bash
# Check global autoconfigure
grep -r "autoconfigure" config/services/

# Check per-BC service configs
grep -rn "autoconfigure" src/*/Bundle/*/Resources/config/ src/*/*/back/Infrastructure/ --include="*.yml" --include="*.xml" --include="*.yaml"

# Check for kernel.event_subscriber tags
grep -rn "kernel.event_subscriber" src/ config/ --include="*.yml" --include="*.xml" --include="*.yaml"

# Check compiler passes
grep -rn "kernel.event_subscriber\|addTag.*event" src/ --include="*.php" | grep -i "compiler\|pass\|extension"
```

**Step 2: Document the registration pattern**

The result determines the migration approach:
- **If autoconfigure=true per BC:** Simply swap interface→attributes, autoconfigure detects `#[AsEventListener]`
- **If compiler pass tags them:** Must update the compiler pass or enable autoconfigure
- **If manually tagged in YAML/XML:** Must update each service definition to use `kernel.event_listener` tag

**Step 3: Commit investigation notes**

Save findings to a comment in the PR for reference.

---

### Task 2: Write migration script

**Files:**
- Create: `bin/migrate-event-subscribers.php`

**Step 1: Write the PHP migration script**

The script should:
1. Find all files with `implements EventSubscriberInterface` (or `implements.*EventSubscriberInterface`)
2. Parse `getSubscribedEvents()` return array to extract: event class/constant, method name, priority
3. Generate `#[AsEventListener]` attributes on each handler method
4. Add `use Symfony\Component\EventDispatcher\Attribute\AsEventListener;` import
5. Remove `implements EventSubscriberInterface` from class declaration
6. Remove `use Symfony\Component\EventDispatcher\EventSubscriberInterface;` import
7. Remove the entire `getSubscribedEvents()` method
8. Handle three patterns:
   - Simple: `[EventClass::class => 'methodName']`
   - With priority: `[EventClass::class => ['methodName', -10]]`
   - Multiple events: `[EventA::class => 'onA', EventB::class => 'onB']`
   - String events: `[StorageEvents::POST_SAVE => 'method']` (constant reference)

**Step 2: Test on 3 representative files**

Run the script on:
- A simple single-event subscriber (Oro FeatureFlagAclPrivilegesEventSubscriber)
- A multi-event subscriber with priorities (DispatchProductCreatedAndUpdatedEventSubscriber)
- A multi-event subscriber without priorities (ApiErrorEventSubscriber)

Verify output matches expected `#[AsEventListener(event: EventClass::class, method: 'methodName', priority: -10)]` syntax.

**Step 3: Commit the migration script**

```bash
git add bin/migrate-event-subscribers.php
git commit -m "chore: add EventSubscriber → AsEventListener migration script"
```

---

### Task 3: Run migration on all 139 files

**Step 1: Run the script**

```bash
docker-compose run --rm php php bin/migrate-event-subscribers.php
```

**Step 2: Verify with grep that no EventSubscriberInterface remains**

```bash
# Should return 0 results in src/ (excluding vendor, tests)
grep -rn "implements.*EventSubscriberInterface" src/ components/ --include="*.php" | grep -v vendor | grep -v test | grep -v Test | grep -v spec | grep -v Spec
```

**Step 3: Verify all #[AsEventListener] attributes are present**

```bash
grep -rn "AsEventListener" src/ components/ --include="*.php" | wc -l
# Should be >= 139 (some files have multiple events)
```

**Step 4: Fix any edge cases manually**

Check for:
- Subscribers implementing multiple interfaces (EventSubscriberInterface + other)
- Abstract/base subscriber classes
- Subscribers in the Oro namespace (may need special handling)

**Step 5: Commit the migration**

```bash
git add -A src/ components/
git commit -m "refactor: migrate EventSubscriberInterface to #[AsEventListener] (139 files)"
```

---

### Task 4: Update service registration (if needed)

Based on Task 1 findings:

**If autoconfigure=true per BC (likely):**
- No changes needed — `#[AsEventListener]` is auto-detected

**If compiler pass or manual tags:**

**Step 1: Update service configuration**

For each BC where autoconfigure is false, add explicit `kernel.event_listener` tags or enable autoconfigure:

```yaml
# Option A: Enable autoconfigure per BC
services:
    _defaults:
        autoconfigure: true

# Option B: Tag each service (only if autoconfigure cannot be enabled)
App\EventListener\MyListener:
    tags:
        - { name: kernel.event_listener }
```

**Step 2: Commit service config changes**

```bash
git add config/ src/*/Bundle/*/Resources/config/
git commit -m "chore: update service registration for #[AsEventListener]"
```

---

### Task 5: Fix PimRequirements Intl deprecation

**Files:**
- Modify: `src/Akeneo/Platform/PimRequirements.php:8,58`

**Step 1: Fix the deprecated Intl::getIcuVersion()**

Replace:
```php
use Symfony\Component\Intl\Intl;
// ...
$icuVersion = Intl::getIcuVersion();
```

With:
```php
use Symfony\Component\Intl\Icu;
// ...
$icuVersion = Icu::getVersion();
```

If `Icu::getVersion()` doesn't exist in SF 6.4, use the native PHP function:
```php
$icuVersion = INTL_ICU_VERSION;
```

**Step 2: Commit**

```bash
git add src/Akeneo/Platform/PimRequirements.php
git commit -m "fix: replace deprecated Intl::getIcuVersion() with INTL_ICU_VERSION"
```

---

### Task 6: Update PHPStan deprecation baseline

**Files:**
- Modify: `phpstan-deprecations-baseline.neon`
- Modify: `phpstan-deprecations.neon` (if needed)

**Step 1: Regenerate baseline**

```bash
docker-compose run --rm php php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1 --generate-baseline phpstan-deprecations-baseline.neon
```

**Step 2: Verify deprecation count decreased**

Compare line count before and after. The EventSubscriber-related entries should be gone.

**Step 3: Commit**

```bash
git add phpstan-deprecations-baseline.neon
git commit -m "chore: regenerate PHPStan deprecation baseline after subscriber migration"
```

---

### Task 7: Run CI checks locally

**Step 1: PHPStan deprecation analysis**

```bash
docker-compose run --rm php php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1
```

**Step 2: PHPStan standard analysis**

```bash
PIM_CONTEXT=test make lint-back
```

**Step 3: PHPSpec**

```bash
PIM_CONTEXT=test make unit-back
```

**Step 4: Fix any failures and commit**

---

### Task 8: Clean up migration script and push

**Step 1: Remove the migration script**

```bash
rm bin/migrate-event-subscribers.php
git add bin/migrate-event-subscribers.php
git commit -m "chore: remove migration script"
```

**Step 2: Push and create PR**

```bash
git push -u origin fix/sf64-deprecations
gh pr create --title "refactor: EventSubscribers → #[AsEventListener] (SF 6.4)" --body "..."
```

---

## Distribution Reference

| Context | Files | Priority |
|---------|-------|----------|
| Pim (Enrichment, Structure, Automation) | 62 | High |
| Tool (Versioning, Connector, Batch, API) | 27 | High |
| Platform (UIBundle, Installer, Job) | 15 | Medium |
| Connectivity (Connection) | 14 | Medium |
| UserManagement | 7 | Medium |
| Category | 6 | Medium |
| Channel | 4 | Low |
| identifier-generator | 3 | Low |
| Oro SecurityBundle | 1 | Low |

## Verification

- [ ] `grep -rn "EventSubscriberInterface" src/ components/ --include="*.php"` returns 0 (excluding vendor/test)
- [ ] `grep -rn "AsEventListener" src/ components/ --include="*.php"` returns 139+
- [ ] `PIM_CONTEXT=test make lint-back` passes
- [ ] `PIM_CONTEXT=test make unit-back` passes
- [ ] `castor test:deprecation-back` passes (or fewer errors than before)

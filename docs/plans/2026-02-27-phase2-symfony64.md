# Phase 2 — Symfony 5.4→6.4 LTS + PHP 8.2→8.3

**Date**: 2026-02-27
**Status**: Ready for implementation
**Effort**: L (2-3 sessions with Claude)
**Deadline**: Already overdue (Symfony 5.4 EOL since Nov 2025)

## Blockers — composer.json

| # | Package | Current | Fix | Effort |
|---|---------|---------|-----|--------|
| 1 | `symfony/proxy-manager-bridge` | `^5.4.0` | Remove (deleted in SF6) | M |
| 2 | `ocramius/proxy-manager` | `2.11.1` | Remove (abandoned) | M |
| 3 | `symfony/flex` | `^1.16.1` | Upgrade to `^2.0` | XS |
| 4 | `extra.symfony.require` | `"5.4.*"` | Change to `"6.4.*"` | XS |

## Blockers — Code

### 5. `Symfony\Component\Cache\DoctrineProvider` removed in SF6

**CRITICAL** — `config/packages/doctrine.yml:42,47`:
```yaml
services:
    doctrine.result_cache_provider:
        class: Symfony\Component\Cache\DoctrineProvider    # REMOVED IN SF6
        arguments: ['@doctrine.result_cache_pool']
    doctrine.system_cache_provider:
        class: Symfony\Component\Cache\DoctrineProvider    # REMOVED IN SF6
        arguments: ['@doctrine.system_cache_pool']
```

Also used in `config/packages/prod/doctrine.yml:5-13` for metadata/result/query cache drivers.

**Fix**: Use `Doctrine\Common\Cache\Psr6\DoctrineProvider` from `doctrine/cache`, or migrate to pool-based config:
```yaml
# Option A: doctrine/cache bridge (minimal change)
doctrine.result_cache_provider:
    class: Doctrine\Common\Cache\Psr6\DoctrineProvider
    arguments: ['@doctrine.result_cache_pool']

# Option B: direct pool (recommended for SF6.4)
doctrine:
    orm:
        metadata_cache_driver:
            type: pool
            pool: doctrine.system_cache_pool
```

### 6. `Request::getContentType()` renamed

`src/Akeneo/Platform/Bundle/UIBundle/Http/FormLoginAuthenticator.php:76`:
```php
&& ($this->options['form_only'] ? 'form' === $request->getContentType() : true);
```

**Fix**: Replace with `$request->getContentTypeFormat()`.

### 7. Lazy Services (11 service definitions)

These use `lazy: true` which requires `symfony/proxy-manager-bridge` in SF5.4. In SF6.4, lazy services use `symfony/var-exporter` ghost objects natively — the `lazy: true` flag still works, but the proxy-manager packages must be removed.

| File | Line | Service |
|------|------|---------|
| `src/Akeneo/Pim/Enrichment/Bundle/Resources/config/serializers_indexing.yml` | 33, 135 | normalizer services |
| `src/Akeneo/Pim/Enrichment/Bundle/Resources/config/serializers_standard.yml` | 36, 81 | normalizer services |
| `src/Akeneo/Pim/Enrichment/Bundle/Resources/config/serializers_storage.yml` | 40, 49 | normalizer services |
| `src/Akeneo/Tool/Bundle/BatchBundle/Resources/config/jobs.yml` | 15, 27 | batch repositories |
| `src/Akeneo/Tool/Bundle/VersioningBundle/Resources/config/event_subscribers.yml` | 11 | versioning subscriber |
| `src/Akeneo/UserManagement/Bundle/Resources/config/event_subscribers.yml` | 11, 46 | user preferences, admin auth |

**Fix**: Keep `lazy: true` — it works in SF6.4 with `symfony/var-exporter`. Just remove `ocramius/proxy-manager` and `symfony/proxy-manager-bridge` from composer.json. Symfony 6.4 auto-detects and uses var-exporter.

## Warnings (non-blocking for SF6.4, will block SF7)

| Issue | File:Line | Fix |
|-------|-----------|-----|
| `LegacyPasswordAuthenticatedUserInterface` + `getSalt()` | `UserInterface.php:23`, `User.php:394` | Migrate to `bcrypt`/`argon2id` (no salt needed) |
| `Request::isXmlHttpRequest()` soft deprecated (SF6.2) | `MassActionController.php:45`, `DatagridViewController.php:46,115,178` | Use `$request->headers->get('X-Requested-With')` |
| `auto_mapping: true` in Doctrine ORM | `config/packages/doctrine.yml:30` | Configure mappings explicitly |

## Already Clean (no action needed)

- ✅ 0 `ContainerAwareCommand` — all migrated
- ✅ 0 `$defaultName` — all migrated to `#[AsCommand]` (PR #28)
- ✅ 0 `@Route` annotations — all use attributes or YAML
- ✅ 0 `TreeBuilder::root()` — modern syntax everywhere
- ✅ 0 `getMasterRequest()` — already uses `getMainRequest()`
- ✅ 0 `kernel.root_dir` — not used
- ✅ 0 `Symfony\Component\EventDispatcher\Event` — uses contracts
- ✅ All Twig uses `{% apply spaceless %}` (not deprecated `{% spaceless %}`)
- ✅ Security config already uses `custom_authenticators` and `password_hashers`
- ✅ No `enable_authenticator_manager` flag (not needed)
- ✅ No `guard:` or `anonymous:` config

## Security Config Inventory

Firewalls (all already using modern authenticators):
```
dev             → security: false
login           → form only
reset_password  → form only
oauth_token     → security: false
oauth2_token    → security: false
openid_public_key → security: false
app_scopes_update → custom_authenticators: [OAuthAuthenticator]
api_index       → security: false
api             → custom_authenticators: [OAuthAuthenticator]
main            → form_login, logout, remember_me
```

The `acl:` config block at bottom of security.yml belongs to `symfony/acl-bundle` (supports SF6). Not a blocker.

---

## PHP 8.2 → 8.3

### `\Serializable` Interface (deprecated 8.1, fatal in 8.4)

| File | Line | Class |
|------|------|-------|
| `src/Oro/Bundle/SecurityBundle/Metadata/EntitySecurityMetadata.php` | 7 | `implements \Serializable` |
| `src/Oro/Bundle/SecurityBundle/Metadata/ActionMetadata.php` | 7 | `implements \Serializable` |
| `src/Akeneo/UserManagement/Component/Model/UserInterface.php` | 23 | `extends \Serializable` |
| `src/Akeneo/UserManagement/Component/Model/User.php` | 235, 252 | `serialize()` / `unserialize()` |
| `src/Oro/Bundle/SecurityBundle/Acl/Domain/RootBasedAclWrapper.php` | 156, 164 | `serialize()` / `unserialize()` |

**Fix**: Replace `implements \Serializable` + `serialize()/unserialize()` with `__serialize()/__unserialize()` magic methods.

### Other PHP 8.2→8.3 patterns

- ✅ 0 `utf8_encode()` / `utf8_decode()`
- ✅ 0 `${var}` string interpolation
- ✅ 0 `get_class()` without arguments

---

## Migration Steps

### Step 1: Pre-migration cleanup (1h)
```bash
docker-compose run --rm php php tools/composer-unused
docker-compose run --rm php php tools/composer-require-checker
```

### Step 2: Fix blockers in code (2h)
1. Fix `DoctrineProvider` in `config/packages/doctrine.yml` and `config/packages/prod/doctrine.yml`
2. Fix `getContentType()` → `getContentTypeFormat()` in `FormLoginAuthenticator.php`
3. Fix `\Serializable` in 5 files

### Step 3: Update composer.json (1h)
```bash
# Remove proxy-manager packages
composer remove ocramius/proxy-manager symfony/proxy-manager-bridge

# Update flex
composer require symfony/flex:^2.0

# Update symfony.require
# In composer.json: "extra": { "symfony": { "require": "6.4.*" } }

# Update all Symfony packages
composer update "symfony/*" --with-all-dependencies
```

### Step 4: Fix remaining issues from composer update (2-4h)
- Resolve dependency conflicts
- Update any service definitions that break
- Fix deprecation warnings in container compilation

### Step 5: Validate (1h + CI time)
```bash
docker-compose run --rm php php bin/console cache:clear
docker-compose run --rm php composer validate --strict
PIM_CONTEXT=test make lint-back
PIM_CONTEXT=test make unit-back
PIM_CONTEXT=test make coupling-back
PIM_CONTEXT=test make acceptance-back
```

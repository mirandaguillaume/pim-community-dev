# Doctrine Annotations Migration Audit

**Date:** 2026-02-22
**Branch:** `chore/doctrine-annotations-audit`
**Scope:** `src/` and `components/` directories

---

## Executive Summary

The Akeneo PIM codebase has **very low direct annotation usage**. The vast majority of Doctrine ORM mapping is handled via **48 external YAML mapping files** (`*.orm.yml`), not inline annotations. Only **2 entity files** use `@ORM\` annotations, and only **2 model files** use `@Assert\` annotations. There are **zero** Symfony routing or serializer annotations.

This makes the annotation-to-attributes migration a **low-effort, low-risk** task.

---

## 1. Doctrine ORM Annotations (`@ORM\`)

### File Count

| Metric | Count |
|--------|-------|
| Files importing `Doctrine\ORM\Mapping as ORM` | 2 |
| Files using `@ORM\` annotations | 2 |

### Annotation Type Breakdown

| Annotation | Count |
|------------|-------|
| `@ORM\Column` | 7 |
| `@ORM\UniqueConstraint` | 2 |
| `@ORM\Table` | 2 |
| `@ORM\Id` | 2 |
| `@ORM\GeneratedValue` | 2 |
| `@ORM\Entity` | 2 |
| `@ORM\OneToMany` | 1 |
| `@ORM\ManyToOne` | 1 |
| `@ORM\JoinColumn` | 1 |
| **Total** | **20** |

### Affected Files

1. **`src/Oro/Bundle/ConfigBundle/Entity/Config.php`** (149 lines)
   - `@ORM\Table`, `@ORM\UniqueConstraint`, `@ORM\Entity`, `@ORM\Id`, `@ORM\Column` (x3), `@ORM\GeneratedValue`, `@ORM\OneToMany`

2. **`src/Oro/Bundle/ConfigBundle/Entity/ConfigValue.php`** (155 lines)
   - `@ORM\Table`, `@ORM\UniqueConstraint`, `@ORM\Entity`, `@ORM\Id`, `@ORM\Column` (x4), `@ORM\GeneratedValue`, `@ORM\ManyToOne`, `@ORM\JoinColumn`

Both files are in the **Oro ConfigBundle** (legacy/upstream code).

---

## 2. Doctrine ORM YAML Mapping Files

The primary mapping strategy in this codebase is **YAML-based external mapping** (`*.orm.yml`).

| Directory | File Count |
|-----------|------------|
| `src/Akeneo/Pim/Structure/Bundle/Resources/config/model/doctrine/` | 16 |
| `src/Akeneo/Pim/Enrichment/Bundle/Resources/config/doctrine/Product/` | 7 |
| `src/Akeneo/Tool/Bundle/BatchBundle/Resources/config/model/doctrine/` | 4 |
| `src/Akeneo/Tool/Bundle/ApiBundle/Resources/config/doctrine/` | 4 |
| `src/Akeneo/Channel/back/Infrastructure/Symfony/Resources/config/doctrine/model/` | 4 |
| `src/Akeneo/UserManagement/Bundle/Resources/config/model/doctrine/` | 3 |
| `src/Akeneo/Platform/Bundle/NotificationBundle/Resources/config/doctrine/` | 2 |
| `src/Akeneo/Category/back/Infrastructure/Symfony/Resources/config/doctrine/model/` | 2 |
| `src/Acme/Bundle/AppBundle/Resources/config/doctrine/` | 2 |
| `src/Akeneo/Pim/Enrichment/Bundle/Resources/config/doctrine/Comment/` | 1 |
| `src/Akeneo/Tool/Bundle/VersioningBundle/Resources/config/model/doctrine/` | 1 |
| `src/Akeneo/Tool/Bundle/FileStorageBundle/Resources/config/model/doctrine/` | 1 |
| `src/Oro/Bundle/PimDataGridBundle/Resources/config/doctrine/` | 1 |
| **Total** | **48** |

**Note:** These YAML mapping files do **not** need to be converted to PHP 8 attributes. They are a separate mapping strategy. However, Doctrine ORM 3.0 will deprecate YAML mapping in favor of attributes or XML. A YAML-to-attributes migration would be a much larger separate effort.

---

## 3. Symfony Validation Annotations (`@Assert\`)

| Metric | Count |
|--------|-------|
| Files importing `Symfony\Component\Validator\Constraints as Assert` | 14 |
| Files actually using `@Assert\` annotations in docblocks | 2 |
| Unique annotation types | 1 (`@Assert\GroupSequenceProvider`) |
| Total annotation usages | 2 |

The 14 files that import `Assert` use it **programmatically** (constructing constraint objects in code), not as docblock annotations. Only 2 files use the annotation syntax:

1. `src/Akeneo/Pim/Structure/Component/Model/Attribute.php` (line 14)
2. `src/Akeneo/Pim/Enrichment/Component/Product/Model/Group.php` (line 17)

### Symfony Validation YAML Config

| File | Location |
|------|----------|
| `validation.yml` | `src/Akeneo/Tool/Bundle/MeasureBundle/back/src/Resources/config/` |
| `validation.yml` | `src/Akeneo/UserManagement/Bundle/Resources/config/` |

---

## 4. Symfony Routing Annotations (`@Route`, `@Method`)

| Metric | Count |
|--------|-------|
| Files using `@Route` or `@Method` | **0** |

Routing is fully handled via YAML/XML config or PHP route definitions. No migration needed.

---

## 5. Symfony Serializer Annotations (`@Groups`, `@SerializedName`, `@Ignore`)

| Metric | Count |
|--------|-------|
| Files using serializer annotations | **0** |

No migration needed.

---

## 6. Other Annotation Libraries

| Library | Count |
|---------|-------|
| JMS Serializer (`@JMS\`) | 0 |
| Gedmo DoctrineExtensions (`@Gedmo\`) | 0 |
| Symfony Serializer (`@Serializer\`) | 0 |

---

## 7. Dependency Versions

| Package | Required | Installed |
|---------|----------|-----------|
| `doctrine/annotations` | `^1.13.2` | `1.14.4` |
| `doctrine/orm` | `^2.9.0` | `2.20.9` |
| `doctrine/persistence` | `^3.0` | `3.x` |
| `rector/rector` | `^0.15.0` | `0.15.25` (in lock, not installed) |

### Key Compatibility Notes

- **Doctrine ORM 2.20.9** fully supports PHP 8 attributes alongside annotations (dual support since ORM 2.9).
- **Doctrine ORM 3.0** (when released) will **remove** annotation support entirely, requiring attributes or XML/YAML.
- **`doctrine/annotations` 1.14.4** is the final 1.x release. Version 2.0 removes the annotation reader (only provides compatibility layer).
- The existing `rector.php` already configures `DoctrineSetList::DOCTRINE_COMMON_20` and `DoctrineSetList::DOCTRINE_ORM_213` for namespace migrations, but does **not** include `ANNOTATIONS_TO_ATTRIBUTES`.

---

## 8. Rector Compatibility Assessment

### Current State
- Rector `0.15.25` is declared in `composer.lock` but **vendor dependencies are not installed** in this worktree.
- The existing `rector.php` handles Doctrine persistence namespace migration only.
- Rector's `DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES` rule set can handle the `@ORM\` to `#[ORM\]` conversion automatically.
- Rector's `SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES` can handle `@Assert\GroupSequenceProvider` to `#[Assert\GroupSequenceProvider]`.

### Rector Dry-Run Status
- **Not performed** because `vendor/bin/rector` is not available (dependencies not installed).
- Once `make dependencies` is run, a dry-run can be executed with:
  ```bash
  docker-compose run --rm php php vendor/bin/rector process \
    --config /tmp/rector-annotations.php \
    --dry-run \
    src/Oro/Bundle/ConfigBundle/Entity/
  ```

### Rector Config for Annotations-to-Attributes
A dedicated config would look like:
```php
<?php
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/components'])
    ->withSets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
```

---

## 9. Estimated Effort

| Task | Files | Effort | Risk |
|------|-------|--------|------|
| Convert `@ORM\` annotations to attributes (2 Oro entity files) | 2 | ~30 minutes | Low |
| Convert `@Assert\GroupSequenceProvider` to attribute (2 model files) | 2 | ~15 minutes | Low |
| Test converted entities (unit + integration) | -- | ~1 hour | Low |
| Remove `doctrine/annotations` dependency (after full migration) | 1 | ~30 minutes | Medium |
| **YAML mapping files to attributes (future, optional)** | **48** | **~2-3 days** | **High** |

**Total for annotation-only migration: ~2 hours**
**Total including YAML mapping migration: ~2-3 days (separate effort)**

---

## 10. Recommended Phased Approach

### Phase 1: Annotation-to-Attributes (Immediate, Low Risk)

**Scope:** 4 files, ~20 annotation instances

1. **Install dependencies** in the worktree (`make dependencies`).
2. **Convert `@ORM\` annotations** in the 2 Oro ConfigBundle entity files:
   - `src/Oro/Bundle/ConfigBundle/Entity/Config.php`
   - `src/Oro/Bundle/ConfigBundle/Entity/ConfigValue.php`
3. **Convert `@Assert\GroupSequenceProvider`** in the 2 model files:
   - `src/Akeneo/Pim/Structure/Component/Model/Attribute.php`
   - `src/Akeneo/Pim/Enrichment/Component/Product/Model/Group.php`
4. **Run Rector** with `ANNOTATIONS_TO_ATTRIBUTES` set for automated conversion.
5. **Validate** with `PIM_CONTEXT=test make lint-back` and `PIM_CONTEXT=test make unit-back`.
6. **Verify** Doctrine schema is unchanged: `docker-compose run --rm php php bin/console doctrine:schema:validate`.

### Phase 2: Evaluate YAML Mapping Migration (Medium Term, Higher Risk)

**Scope:** 48 YAML mapping files across 13 directories

1. **Assess Doctrine ORM 3.0 timeline** -- YAML mapping is supported in ORM 2.x but deprecated path for 3.0.
2. **Prioritize by bounded context** (start with smallest: Notification, FileStorage, Versioning).
3. **Use Rector + manual review** -- Rector cannot auto-convert YAML to attributes; this requires manual work or custom tooling.
4. **Test each context independently** using bounded context make targets.

### Phase 3: Remove `doctrine/annotations` Dependency (After Full Migration)

1. Remove `doctrine/annotations` from `composer.json`.
2. Remove any `AnnotationReader` configuration in Symfony DI.
3. Ensure no third-party bundles still require `doctrine/annotations`.
4. Run full CI pipeline.

---

## 11. Files Reference

### Files with `@ORM\` annotations (convert to attributes)
```
src/Oro/Bundle/ConfigBundle/Entity/Config.php
src/Oro/Bundle/ConfigBundle/Entity/ConfigValue.php
```

### Files with `@Assert\` annotations (convert to attributes)
```
src/Akeneo/Pim/Structure/Component/Model/Attribute.php
src/Akeneo/Pim/Enrichment/Component/Product/Model/Group.php
```

### YAML mapping files (future migration, separate effort)
```
src/Akeneo/Pim/Structure/Bundle/Resources/config/model/doctrine/   (16 files)
src/Akeneo/Pim/Enrichment/Bundle/Resources/config/doctrine/         (8 files)
src/Akeneo/Tool/Bundle/BatchBundle/Resources/config/model/doctrine/  (4 files)
src/Akeneo/Tool/Bundle/ApiBundle/Resources/config/doctrine/          (4 files)
src/Akeneo/Channel/back/Infrastructure/Symfony/Resources/config/     (4 files)
src/Akeneo/UserManagement/Bundle/Resources/config/model/doctrine/    (3 files)
src/Akeneo/Platform/Bundle/NotificationBundle/Resources/config/      (2 files)
src/Akeneo/Category/back/Infrastructure/Symfony/Resources/config/    (2 files)
src/Acme/Bundle/AppBundle/Resources/config/doctrine/                 (2 files)
src/Akeneo/Tool/Bundle/VersioningBundle/Resources/config/            (1 file)
src/Akeneo/Tool/Bundle/FileStorageBundle/Resources/config/           (1 file)
src/Oro/Bundle/PimDataGridBundle/Resources/config/doctrine/           (1 file)
```

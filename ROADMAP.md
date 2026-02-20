# Roadmap — Akeneo PIM Community Edition Fork

## Phase 1 : Modernisation des dépendances (en cours)

### Mergé
- [x] **PR #6** — Upgrade doctrine/persistence 2.x → 3.x
- [x] **PR #14** — Upgrade dompdf/dompdf 2.x → 3.x

### En cours (CI en validation)
- [ ] **PR #11** — Upgrade rector 0.15 → 2.x + phpstan 1.x → 2.x
  - Includes phpstan-symfony 2.x, phpstan-webmozart-assert 2.x, phpstan-deprecation-rules 2.x
  - PHPStan config migrated to 2.x format (analyseAndScan, containerXmlPath, inline ignores)
- [ ] **PR #13** — Upgrade semver-safe dependencies (php-cs-fixer, security-acl)
- [ ] **PR #15** — Upgrade lcobucci/jwt 4.x → 5.x
  - Immutable Builder pattern, PSR-20 clock, spec refactored
- [ ] **PR #16** — Upgrade maennchen/zipstream-php 2.x → 3.x
  - Option\Archive → named constructor args, sendHttpHeaders: false
- [ ] **PR #17** — Upgrade google/cloud-pubsub 1.x → 2.x
  - Transitive deps updated (google/gax, google/cloud-core, google/protobuf)
  - CI credentials issue to investigate
- [ ] **PR #18** — Upgrade webmozart/assert 1.x → 2.x
  - Stricter type checking adapted (Assert::notNull guards, string casts)

### Abandonné
- ~~Upgrade monolog 2.x → 3.x~~ — Bloqué par Symfony 5.4 (monolog-bundle 4 requiert Symfony 7.3+)

## Phase 2 : Upgrade Symfony (prochaine étape majeure)

- [ ] Symfony 5.4 → 6.4 LTS
  - Débloque monolog 3, et de nombreuses dépendances modernes
  - Nécessite un audit complet des dépréciations Symfony
  - Gros chantier : security-bundle, form, messenger, etc.
- [ ] Symfony 6.4 → 7.x (optionnel, après stabilisation)

## Phase 3 : Modernisation du code

- [ ] Suppression des dépendances abandonnées (doctrine/annotations, doctrine/cache, symfony/security-guard)
- [ ] Migration PSR-4 complète (nettoyer autoload.psr-0)
- [ ] PHP 8.3+ features (typed class constants, readonly classes partout, etc.)
- [ ] Remplacement des Oro bundles legacy

## Phase 4 : Améliorations fonctionnelles

- [ ] API modernisée (OpenAPI 3.x, versioning)
- [ ] Frontend : migration React 17 → 18+
- [ ] Performance : optimisation Elasticsearch, queries Doctrine
- [ ] DX : meilleur onboarding développeur, documentation à jour

## Notes techniques

- **PHP** : 8.2 minimum (CI testé), objectif 8.3+
- **Docker** : Toutes les commandes PHP via `docker-compose run --rm php php`
- **CI** : GitHub Actions, ~50 jobs par PR (phpspec, phpunit x6, behat x15, deptrac x6, front)
- **Architecture** : Mix hexagonal (bounded contexts modernes) et legacy (Component/Bundle)
- **Licence** : OSL 3.0 / GPL (open-source, fork autorisé)

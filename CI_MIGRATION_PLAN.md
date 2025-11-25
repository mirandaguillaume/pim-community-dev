# Plan de migration CI (CircleCI → GitHub Actions)

## Suivi d’avancement
- [x] Cartographier la configuration CircleCI existante (workflows, jobs, scripts `.circleci`, caches composer/yarn/images, services MySQL/ES/MinIO/Pub/Sub, paramètre `run-database-tests`, déclencheurs PR/master, filtres de chemin).
- [x] Concevoir l’architecture GitHub Actions (séparation PR/master, matrices éventuelles, runner Ubuntu, stratégie de cache via actions/cache, publication d’images éventuelle sur GHCR, équivalents aux paramètres/filtres de chemins).
- [x] Traduire les services et l’initialisation (`docker compose` ou services GHA, gestion permissions/chown, secrets/vars GitHub, réutilisation des scripts Make/bash).
- [x] Implémenter les workflows `.github/workflows/*.yml` (lint, tests back, tests front, e2e si besoin), portage scripts CircleCI, artefacts (logs/JUnit), parallélisme/matrice.
- [x] Optimiser (caches fins composer/yarn/images, actions officielles checkout/setup-node/setup-php/cache, réduction temps de boot docker).
- [ ] Valider (exécution locale via `act` ou PR draft, vérifier permissions/chemins/résultats, ajustements).
- [ ] Décommissionner CircleCI (retirer/archiver `.circleci`, mettre à jour README/badges, désactiver projet CircleCI).

## Architecture GHA proposée
- Workflows
  - `ci-pr.yml` : `pull_request` (toutes branches hors master) + `workflow_dispatch`, `concurrency` pour annuler les runs obsolètes; `paths-filter` pour détecter `upgrades/**` → set `run_database_tests=true`.
  - `ci-main.yml` : `push` sur `master` (et branches de release si besoin) avec mêmes jobs que PR, sans approbation.
  - `connectivity.yml` : `pull_request` sur branches `^(?i)(CXP|OCT)-.*` (squad Octopus), avec jobs front/back dédiés.
  - `nightly-docker.yml` : `schedule` (cron) pour reconstruire/pusher l’image PHP (GHCR ou registry existante), et éventuellement exécuter un smoke (build/test minimal).
  - `manual-deploy.yml` (optionnel si besoin) : `workflow_dispatch` pour déclencher les jobs de build/push prod (remplace `build_prod`/`test_deploy` CircleCI) avec secrets GCP/GCR stockés dans GitHub Secrets.

- Jobs (workflow PR/main)
  - `build` (Ubuntu, Docker disponible) : checkout, cache composer/yarn, `docker/build-push-action` ou `docker build` avec `cache-to/from type=gha`, `make dependencies assets css front-packages javascript-dev`, produire et uploader artefacts `php-pim-image.tar`, `vendor`, `node_modules`, libs front `front-packages/*/lib`, éventuellement `var` caches. Sert de base aux jobs suivants.
  - `front-static` : récupère artefacts (ou recalcule via caches), exécute `make javascript-dev-strict lint-front unit-front`.
  - `back-static` : récupère image+vendor, lance `make static-back deprecation-back lint-back coupling-back unit-back acceptance-back`.
  - `phpunit` : matrix (ex: `shard: [0..19]`) pour distribuer `make pim-integration-back` et `make end-to-end-back` avec variable d’index (adapter scripts de split si nécessaire). Utilise image + DB services via `docker compose` (`APP_ENV=test C='httpd mysql elasticsearch object-storage pubsub-emulator gcs-emulator' make up`).
  - `behat-legacy` : matrix sur suites (`all`, `weasel`, `chipmunk`, `raccoon` selon branche) avec `APP_ENV=behat` compose + `make end-to-end-legacy`.
  - `data-migrations` : `APP_ENV=test` compose + `make migration-back`.
  - `cypress` : `APP_ENV=prod` compose + `make database` (icecat fixtures) + `make end-to-end-front`, artefacts vidéos/captures.
  - `database-structure` : conditionné par filtre `upgrades/**` → `make test-database-structure` (remplace `run-database-tests` param).
  - (Optionnel) `performance`, `onboarder`, `job-declaration` jobs si on souhaite couvrir l’équivalent des jobs CircleCI moins fréquents.
  - `ci-summary` : job final qui dépend de tous pour synthèse/notifications.

- Jobs (nightly-docker)
  - `build-docker` : build image PHP prod via `make php-image-prod` ou `docker/build-push-action`, push GHCR/GCR (secrets), sauvegarde tag (sha/short/date) en artefact.
  - `smoke` (optionnel) : lancer `make up` minimal + healthcheck.

- Jobs (connectivity)
  - `install_front_dependencies`, `install_back_dependencies`, `build`, `front lint/unit/build`, `back unit/integration/e2e/behat` alignés sur workflow Octopus existant; réutiliser cache/artefacts du job `build`.

- Mutualisation/infra
  - Runners : `ubuntu-22.04` avec `docker`/`docker compose`.
  - Cache : `actions/cache` pour `~/.composer`, `~/.cache/yarn`, et cache docker `type=gha` pour l’image PHP; clés basées sur `composer.lock`/`yarn.lock` + `CACHE_VERSION`.
  - Artefacts : `actions/upload-artifact` pour `php-pim-image.tar`, `vendor`, `node_modules`, libs front, logs/tests (`var/tests`, `cypress/*`).
  - Services : privilégier `docker compose` (déjà piloté par `make up`) plutôt que `services:` GHA pour rester aligné avec les scripts.
  - Sécurité/secrets : variables CircleCI (`SLACK_*`, `GCLOUD_*`, etc.) migrées vers `Secrets`/`Variables` GitHub; approbations remplacées par `environment` protections ou séparation `workflow_dispatch`.
  - Concurrency : `concurrency: ${{ github.workflow }}-${{ github.ref }}` avec `cancel-in-progress: true` pour PR.

## Notes
- Mettre à jour la checklist ci-dessus à chaque jalon franchi.
- Documenter brièvement dans chaque PR les changements GHA et ce qui remplace l’existant CircleCI.

### Workflows ajoutés
- `.github/workflows/ci.yml` : pipeline PR/push master (build image, deps, front/back static, phpunit, behat, migrations, cypress, test structure conditionnel).
- `.github/workflows/connectivity.yml` : pipeline branches CXP/OCT (front lint/unit/build, back unit/int/e2e, behat).
- `.github/workflows/nightly-docker.yml` : build quotidien de l’image PHP (artefact + push GHCR optionnel via `PUBLISH_IMAGE`).

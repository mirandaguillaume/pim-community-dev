# Reprise — pim-community-dev

## À quoi sert le projet

Fork personnel public de `akeneo/pim-community-dev` (Akeneo PIM Community Edition : PHP 8.4,
Symfony 6.4 LTS, Doctrine ORM 3, React 18). Sert de terrain de modernisation : montée des
dépendances (Symfony 6.4, PHPUnit 10 à la place de PHPSpec), migration du front (AMD vers ESM,
Backbone vers React sur les datagrids, Redux Toolkit / react-query), migration des tests Behat
vers Playwright, durcissement CI et sécurité, couverture de tests et mutation testing.

## État au 2026-11-02

- Branche courante : `feat/tiptap-editor` (4 commits propres à la branche, publiés sur
  `origin/feat/tiptap-editor`). Elle remplace l'éditeur riche du design system (chaîne
  draft-js / react-draft-wysiwyg, XSS sans correctif publié) par Tiptap. **PR #520 ouverte** ;
  CI verte sauf le job « Launch tests on the DSM » (cause non examinée) ; la branche a 7 commits de
  retard sur `origin/master`.
- `origin/master` : dernier commit du 2026-11-01 (#529, bump de dépendance). `master` local est
  en retard (dernier commit local du 2026-10-21) : s'aligner sur `origin/master` avant tout travail.
- Autres PR ouvertes : trois mises à jour Dependabot (#522, #523, #528).
- Arbre de travail propre. Aucune branche `wip/sauvegarde-*` ici. Sauvegardes : 8 branches
  distantes `origin/stash/2026-02-27-11` … `origin/stash/2026-07-15-1`, et **12 entrées de
  `git stash` locales** (anciennes branches de montée de version, de migration ESM, etc.)
  qui n'existent que dans `.git` local.
- Familles de branches locales (327, la quasi-totalité déjà fusionnée par squash dans `master`,
  donc `git diff master...branche` les montre à tort comme non fusionnées ; vérifier l'état de la PR) :
  `c1/*` (~55, datagrid produit Backbone vers React : filtres, pagination, cellules),
  `feat/amd-to-esm-*` et `feat/front-b*` (migration ESM), `pw/*` (~30, scénarios Behat portés en
  Playwright), `test/jest-coverage-phase*` (~35) et autres `test/*` (couverture, gardes),
  `ci/*` (~25, fiabilité et vitesse de la CI), `chore/*`, `fix/*`, `harden/*` (e2e),
  `security/*` (CVE, en-têtes HTTP), `upgrade/*` (Doctrine, prettier, abstraction SGBD),
  `migrate/phpspec-*`, `behat/*`, `audit/*`, `probe/*`, `spike/*`, `wt/*` (worktrees d'agents jetables).

## Installer et lancer

PHP s'exécute uniquement dans Docker (un hook l'impose). Les tâches passent par **Castor**
(`castor list` énumère tout ; il n'y a plus de Makefile depuis #128).

```bash
docker build --target dev -t akeneo/pim-php-dev:master .
docker-compose up -d                     # services : php, httpd, mysql, elasticsearch, object-storage, ...
castor test:lint-back                    # PHPStan + php-cs-fixer + lints par contexte
castor test:unit-back
castor test:coupling-back
castor test:acceptance-back
castor <contexte>:unit-back              # ex. category:unit-back, connectivity-connection:lint-back
castor back:fix-cs-back
yarn lint && yarn unit                   # front ; E2E : npx playwright test
```

Un seul test PHP : `APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit -c . --filter NomDeClasse`.
Documentation de test amont : `internal_doc/tests/running_the_tests.md` (dépôt akeneo).
CI : GitHub Actions (`.github/workflows/ci.yml`, plusieurs autres fichiers dont `dsm-test.yml`),
runners auto-hébergés, jobs filtrés par chemins ; seul `ci-success` est requis.

## Où j'en étais et prochaines étapes

1. Débloquer la PR #520 : lire l'échec de « Launch tests on the DSM » (workflow `dsm-test.yml`),
   corriger, rebaser sur `origin/master`, activer l'auto-merge (`gh pr merge --auto --squash`).
   Le commit annonce 14 alertes sur 15 fermées ; reste `node-fetch@2.6.1` via `jest-fetch-mock`.
2. Faire relire visuellement la barre d'outils Tiptap dans Storybook (les tests Jest ne tournent
   qu'en CI).
3. Ensuite, d'après `ROADMAP.md` (révision du 2026-10-20), palier 2 : les 171 avertissements
   eslint de Category (12 `exhaustive-deps` en premier), le build publié du design system qui
   n'est consommable que dans un bundler configuré (barrel `__exportStar`, assets SVG hors `lib/`).
4. Reste connu de la suite `End_to_End` PHPUnit : 4 tests exclus (`@group e2e_known_failure`),
   dont 2 à diagnostiquer (calculs de score DQI).
5. Chantiers explicitement non démarrés : Backbone vers React restant (phase C2/C3), typage des
   `.js` (D1), phases 4 à 7 du plan de couverture Jest, burn-down de la baseline PHPStan.
   Ne pas réécrire `filters-manager.js` ni créer `select-row-filter-react.ts` (voir `ROADMAP.md`).

## À fournir hors dépôt

- Docker et docker-compose ; `.env.test` et `.env.behat` sont versionnés, aucune autre variable
  obligatoire n'est documentée. Un `.envrc` local ignoré peut exporter
  `GITHUB_PAT_TOKEN` (jeton GitHub pour les outils CLI), à recréer au besoin.
- Fichiers locaux ignorés : `docker-compose.override.yml` (surcharge de dev), `seed/`, `var/`,
  `vendor/`, `node_modules/`.
- Compte GitHub avec droits sur le fork (la branche `master` est protégée, on passe par PR) ;
  runners CI auto-hébergés pour reproduire la CI.

## Pièges connus

- Ne jamais lancer Jest en local : il fait tomber la machine ; la CI est le seul juge des tests Jest.
  Substituts : sonde plain-node + jsdom, `ts.transpileModule`, `enhanced-resolve`.
- `public/bundles/` ou `var/` appartenant à root (builds Docker) peuvent bloquer un `git checkout` ;
  les supprimer via `docker-compose run --rm -T --user root node sh -c 'rm -rf -- <chemin>'`.
  `git status` sous-compte ce cas : énumérer avec `find`. Ne pas utiliser `git stash --include-untracked` ici.
- Un PR ne contenant que de la documentation ne peut pas fusionner (`paths-ignore` : `ci-success`
  n'arrive jamais) : embarquer les docs dans un PR de code.
- Un symptôme encore présent ne prouve pas que personne n'y travaille : lister les PR ouvertes
  (`gh pr list --state open`) avant d'écrire un correctif.
- Deux garde-fous étaient « éteints » plusieurs fois (tests vacuous, `php_errors.throw`,
  `failOnRisky="false"`) : chercher l'interrupteur avant d'ajouter un nouveau garde.
- Ne jamais pousser sur `master`, ne jamais contourner un hook ou une protection de branche.
- Le dépôt versionne quatre lockfiles (dont ceux du design system et de son projet d'exemple) à
  mettre à jour séparément : Dependabot n'en bumpe qu'un par entrée.
- Le job Behat peut se bloquer dans « Setup test database » (72 min) : annuler puis relancer
  (`gh run cancel`, attendre `completed`, `gh run rerun --failed`).
- Suite `End_to_End` en local : lancer avec `APP_ENV=test` placé avant `docker compose`,
  et rendre `var/cache/test/sql-dump` inscriptible par l'utilisateur du conteneur, sinon 100 %
  des tests tombent en erreur (ce qui n'est jamais de la dette, seulement l'environnement).

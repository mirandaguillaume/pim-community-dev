# Reprise — pim-community-dev

## À quoi sert le projet

Fork personnel public de `akeneo/pim-community-dev` (Akeneo PIM Community Edition : PHP 8.4,
Symfony 6.4 LTS, Doctrine ORM 3, React 18). Sert de terrain de modernisation : montée des
dépendances (Symfony 6.4, PHPUnit 10 à la place de PHPSpec), migration du front (AMD vers ESM,
Backbone vers React sur les datagrids, Redux Toolkit / react-query), migration des tests Behat
vers Playwright, durcissement CI et sécurité, couverture de tests et mutation testing.

## État au 2026-11-24 (soir)

- **Toutes les PR de la journée sont fusionnées** (#522 → #546, aucune ouverte). Les marquantes :
  - **#520 / #533** Tiptap remplace draft-js dans le design system (barre d'outils réparée par
    `useEditorState`, marges de l'éditeur) ; **#532** 12 `exhaustive-deps` de Category, dont deux
    vrais défauts.
  - **#534** la suite du design system (`test-dsm`) compte enfin dans `ci-success` ;
    **#540** le DSM publie un double build CJS + ESM chargeable hors bundler (`yarn lib:check`).
  - **#535** lock Composer (17 avis) + correctif `executeStreamRequest` (76 tests E2E) ;
    **#537** tests DQI E2E réactivés ; **plus aucune exclusion E2E connue**.
  - **#536** react-router 7.18 ; **#541** 9 alertes Dependabot high (babel-minify mort retiré,
    résolutions) ; **#542** victory 38.
  - **#539 / #546** deux scénarios Behat instables de `add_product_model_children.feature` portés
    vers Playwright (lignes 174 et 56).
- **Sécurité, mise en place du jour** :
  - CodeQL (default setup, JS/TS + Actions) ajouté par l'utilisateur. **#544** ferme 12 alertes JS
    (Bootstrap 2 data-api, `pim-fileinput`, `uuid()` cryptographique, select2) ; **#545** assainit
    le HTML riche chargé par Summernote avec DOMPurify (`window.DOMPurify` posé par
    `globals-prelude.js` : rspack n'active pas l'AMD) ; **#543** permissions minimales sur 4
    workflows et suppression des workflows imbriqués du DSM (jamais exécutés).
  - **5 alertes CodeQL laissées ouvertes volontairement** (choix utilisateur, inatteignables dans
    l'usage du PIM) : #22 tooltip `html`, #24 palette Summernote, #26/#27/#29 chemin iframe de
    jquery.form. Justifications prêtes dans le rapport de tri (fichier:ligne).
  - **Semgrep réparé par #543** : il n'avait **jamais** scanné (ruleset `p/symfony` inexistant →
    exit 7 masqué par `|| true`, upload SARIF refusé depuis mai). PHP seul désormais (CodeQL couvre
    le JS), catégorie `semgrep-php`. **Premier vrai scan : 38 résultats**, en cours de tri.
- Les deux `.tar` déchiffrés de la sauvegarde (`~/A-EMPORTER-2026-11-0{2,6}.tar`) sont **en clair**
  dans `~` et contiennent les clés SSH : à supprimer (les `.gpg` restent) — attend l'accord.
- Beaucoup de worktrees sous `~/claude-worktrees/pim-community-dev/` correspondent à des PR
  fusionnées : `git worktree list`, puis supprimer ceux dont la branche est fusionnée — attend l'accord.
- La branche `upgrade/sgbd-abstraction` (mars) est **abandonnée** : à ignorer.

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

Quatre chantiers lancés en parallèle le 2026-11-24 au soir, chacun dans son worktree, **commits
locaux non poussés** : relire, pousser, ouvrir la PR avec auto-merge.

1. **Tri des 38 résultats Semgrep PHP** — worktree `semgrep-triage`, branche
   `security/semgrep-php-triage`. Priorités : `base_convert` dans `UserManagement/Component/Model/User.php`
   (jeton ?), requête Doctrine de `AbstractItemCategoryRepository.php:142`, variables Twig dans des
   `<script>`, 8 `dangerouslySetInnerHTML`. Les faux positifs non supprimables en ligne seront à
   rejeter sur GitHub (onglet Security) avec la justification.
2. **Racine du piège Jest `moduleDirectories`** — worktree `jest-resolve`, branche
   `test/jest-resolve-nested-deps`. 11 configs listent `<ROOT>/node_modules` : Jest résout alors toute
   dépendance imbriquée vers la copie racine (cause du crash `TextEncoder` de #541). Preuve par
   `jest-resolve`, pas par Jest (interdit en local). Risque : doublons de React — la CI tranche.
3. **Portage du reste de `add_product_model_children.feature` vers Playwright** — worktree
   `port-pm-children`, branche `test/port-add-product-model-children-rest`, **basée sur la branche de
   #546 avant sa fusion en squash : la rebaser sur master** avant de pousser.
4. **Lots eslint Category** (159 : 96 `no-explicit-any`, 36 `await-thenable`, 19 `require-await`,
   8 `no-console`) — worktree `category-eslint`, branche `chore/category-eslint-backlog`. Attention
   aux `async` retirés (type de retour qui change).
5. Chantiers explicitement non démarrés : Backbone vers React restant (phase C2/C3), typage des
   `.js` (D1), phases 4 à 7 du plan de couverture Jest, burn-down de la baseline PHPStan,
   assainissement côté serveur du texte riche (DOMPurify ne protège que l'éditeur).
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
- `gh` répond 401 alors que `gh auth status` est connecté : le `GITHUB_TOKEN` exporté par `.envrc`
  (ancien jeton révoqué) passe avant le trousseau. `unset GITHUB_TOKEN GH_TOKEN`, ou renouveler
  le jeton dans `.envrc`.
- Le hook pre-push s'exécute dans le répertoire du projet de la session, pas dans le worktree
  qu'on pousse : un clone principal resté sur une vieille branche fait échouer le push d'une
  autre branche. Garder le clone principal sur `master` à jour.
- Storybook du design system : il ne compile pas sans les dépendances *peer* (`styled-components`,
  React) — `dsm-test.yml` les ajoute par `yarn add --dev` avant `yarn install` (voir étape 2).
- Ne jamais lancer deux `yarn install` en parallèle : ils se disputent le cache, et yarn échoue en
  « Integrity check failed » sur une archive vide (`sha1-2jmj7l5rSw0yVb/vlWAYkK/YBwk=` = SHA-1 du
  vide) — ce n'est pas une corruption de paquet.
- Ne pas rédiger de trailer `Co-Authored-By` ni de mention d'outil dans les commits et PR ; un
  squash recopie les messages de tous les commits de la branche dans `master`.

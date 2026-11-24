# Reprise — pim-community-dev

## À quoi sert le projet

Fork personnel public de `akeneo/pim-community-dev` (Akeneo PIM Community Edition : PHP 8.4,
Symfony 6.4 LTS, Doctrine ORM 3, React 18). Sert de terrain de modernisation : montée des
dépendances (Symfony 6.4, PHPUnit 10 à la place de PHPSpec), migration du front (AMD vers ESM,
Backbone vers React sur les datagrids, Redux Toolkit / react-query), migration des tests Behat
vers Playwright, durcissement CI et sécurité, couverture de tests et mutation testing.

## État au 2026-11-24

- **#520 (Tiptap) fusionnée** le 2026-11-23 (`bfe800f64e`). L'échec « Launch tests on the DSM »
  était un vrai bug, pas un test fragile : Tiptap 3 ne re-rend plus à chaque transaction, la barre
  d'outils restait figée sur son premier état ; corrigé par `useEditorState` (voir `ROADMAP.md`).
- **PR #532 ouverte, auto-merge armé** (`fix/category-exhaustive-deps`) : les 12
  `react-hooks/exhaustive-deps` de Category soldés (eslint 171 → 159), dont deux vrais défauts —
  le formulaire d'édition de catégorie pouvait effacer des modifications non enregistrées
  (`EditCategoryProvider` sans `useMemo`), et une fermeture périmée pouvait perdre un déplacement
  dans l'arbre. Embarque aussi la mise à jour de `ROADMAP.md`. Si la CI la refuse : lire le job,
  la cause est dans un des 6 fichiers du diff.
- PR Dependabot ouvertes, toutes sur le design system : #522, #523, #528, #531.
- **Ce clone a été restauré** (clone du 2026-03-20, plus l'archive `A-EMPORTER-2026-11-02` :
  `.envrc` et les 4 stashes du bundle `pim-community-dev-stashes-0-2-3-4.bundle`, réimportés en
  `git stash` et en `refs/stash-export/*`). Les 327 branches locales d'avant n'existent plus et
  n'ont pas à être recréées : tout ce qui comptait est sur `origin`. `master` local est aligné sur
  `origin/master`.
- Les deux `.tar` déchiffrés de la sauvegarde (`~/A-EMPORTER-2026-11-0{2,6}.tar`) sont **en clair**
  dans `~` et contiennent les clés SSH : à supprimer une fois la restauration finie (les `.gpg`
  restent).
- Worktrees (`git worktree list`) :
  `~/claude-worktrees/category-exhaustive-deps` (PR #532, garder jusqu'à la fusion),
  `~/claude-worktrees/pim-community-dev/reprise` (cette note), `../pim-tiptap` (#520 fusionnée,
  supprimable avec la branche locale `tiptap-rebased-backup`), et deux worktrees ESM de juin
  (`../pim-community-dev-wip-esm-b5`, `../pim-community-dev-wip-esm-flip-46`) entièrement dépassés
  par master, supprimables.
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

1. Vérifier que **#532** (Category), **#533** (marges de l'éditeur Tiptap) et **#534** (suite du
   design system rendue requise) ont fusionné (`gh pr view 532` etc.) ; sinon lire l'échec et
   corriger. #532 avait une régression (effets du nœud d'arbre relancés sur changement d'`id`),
   corrigée par `504879ad3b`. **#534** : le workflow du design system était rouge sur toutes les
   branches depuis le 2026-11-15 (Playwright refuse les runners Ubuntu 26.04) sans rien bloquer ;
   il est maintenant appelé par `ci.yml` (`test-dsm`), compte dans `ci-success` et tourne sur
   `ubuntu-24.04` épinglé. Si `test-dsm` échoue sur #534, c'est la première exécution du rendu
   Storybook + a11y depuis neuf jours : lire le log, un écart peut dater de #520.
2. ~~Relire visuellement la barre d'outils Tiptap dans Storybook~~ — **fait le 2026-11-24** : barre
   d'outils OK ; la relecture a trouvé la zone de texte sans marge ni bornes de hauteur (sélecteurs
   `.rdw-*` morts), corrigé par **#533** (auto-merge armé). Pour relancer Storybook plus tard :
   ```bash
   cd front-packages/akeneo-design-system
   yarn add --dev react@^18.3.1 react-dom@^18.3.1 styled-components@^5.1.1  # comme dsm-test.yml
   git checkout -- package.json yarn.lock                                     # ne pas committer ça
   yarn storybook:start   # puis http://localhost:6006/?path=/story/components-inputs-textarea-input--rich-text-editor
   ```
   Vérifier : « Title 2 » puis « Normal » s'affichent dans la liste après le choix ; boutons gras /
   italique / listes / lien actifs après clic ET selon la position du curseur ; taille et police
   affichées après le choix ; variantes lecture seule et invalide.
3. **Le build publié du design system n'est consommable qu'à l'intérieur d'un bundler** (palier 2
   de `ROADMAP.md`) — prochain chantier, pas commencé : exports nommés explicites à la place du
   barrel `__exportStar`, et SVG copiés dans `lib/`. Mesurer d'abord sur un `lib/` frais (sonde
   node nue qui hooke `Module._resolveFilename` pour les assets, plus un build Vite du projet
   d'exemple), et tester contre le lockfile du projet d'exemple.
4. Category, lots eslint restants (159) : 96 `no-explicit-any`, 36 `await-thenable`, 19
   `require-await`, 8 `no-console`.
5. Reste connu de la suite `End_to_End` PHPUnit : 4 tests exclus (`@group e2e_known_failure`),
   dont 2 à diagnostiquer (calculs de score DQI).
6. Chantiers explicitement non démarrés : Backbone vers React restant (phase C2/C3), typage des
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

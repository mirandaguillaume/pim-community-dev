# Roadmap — Akeneo PIM Community Edition Fork

**Dernière révision : 2026-10-19.** La version précédente datait du 2026-02-20 et n'avait jamais
été relue : elle annonçait comme « prochaine étape majeure » un upgrade Symfony livré depuis, et
listait en cours des PR numérotées #11 à #18 alors que le dépôt en est à #494. Chaque état ci-dessous
a été mesuré dans l'arbre, pas déduit de l'historique.

## Où en est le fork

| | mesuré |
|---|---|
| PHP | `8.4.*` dans `composer.json`, 8.4.25 sur les runners |
| Symfony | `^6.4` sur les 37 paquets `symfony/*` |
| Doctrine ORM | `^3.6`, mapping en attributs PHP 8 |
| PHPUnit | `^10.5` — PHPSpec entièrement retiré |
| PHPStan / Rector | 2.x tous les deux |
| React | `^18.3.1` |
| CI | behat ×10, phpunit-integration ×6, playwright ×4, front-unit ×2 |

## Ce que l'ancienne feuille de route annonçait

- **Phase 1 (dépendances)** — terminée. Les six PR « en cours » ont été résolues ou remplacées.
- **Phase 2 (Symfony 5.4 → 6.4 LTS)** — **faite**, et monolog 3 n'est plus bloqué.
- **Phase 3** — partielle : `doctrine/cache` et `symfony/security-guard` ont disparu, les annotations
  Doctrine ont été migrées en attributs dès le 2026-02-24, mais `autoload.psr-0` et le paquet
  `doctrine/annotations` sont toujours là (voir Palier 2).
- **Phase 4** — React 17 → 18 faite. Le reste n'a pas bougé.

---

Les paliers ci-dessous classent les chantiers par **état de mesure**, pas par taille : ce qui est prêt
à livrer, ce dont on connaît le coût, et ce qu'il faut sonder avant de décider.

## Palier 1 — prêt, borné, vérifié

- [ ] **Six devDependencies mortes** — `madge`, `cucumber-junit`, `bundle-loader`, `text-loader`,
      `exports-loader`, `jest-watch-typeahead`. Chacune vérifiée par grep exhaustif (hors
      `node_modules`, `vendor`, `var`, `.grepai`, `yarn.lock`) et par `git log -S`. `madge` à elle
      seule enraçinait 12 chemins d'alerte ; ses mentions restantes sont des `npx madge` dans des
      plans terminés, donc elles continuent de fonctionner. Même forme que #493.
- [ ] **Clé du cache yarn de `prepare`** — défaut **structurel**, cause d'un incident **non épinglée**.
      Le cache embarque `front-packages/*/lib`, une sortie de build, avec une clé réduite à
      `hashFiles('yarn.lock')` : une modification de la source du design system ne l'invalide donc
      pas, et un `lib/` périmé peut être servi aux tests. C'est vrai par construction, indépendamment
      de tout incident.
      Ce que ça a coûté une fois : #492 a échoué sur `test-front-packages` avec un `lib/` qui ne
      reflétait pas sa source (`dsm:build` en 1,9 s, invraisemblable pour une compilation complète),
      puis a reverdi après une simple remise à jour de branche. **Cette guérison ne disculpe pas le
      cache** : un défaut qui dépend de l'entrée présente à un instant donné est intermittent par
      nature. Mesure directe à faire avant de corriger — comparer le `lib/` de l'artefact
      `front-packages-lib` avec une compilation fraîche sur le même commit.

## Palier 2 — coût connu, chantier réel

- [ ] **Remplacer la chaîne draft-js** — 11 des 13 chemins d'alerte runtime restants, dont le seul
      XSS et le seul paquet sans correctif publié (`react-draft-wysiwyg`, non maintenu). #486 a déjà
      rendu le vecteur inatteignable en retirant les boutons `embedded` et `image`. Le remplacement
      corrige en plus le défaut de valeur non contrôlée que son consommateur contourne par une `key`
      composite. Coût : nouvelle dépendance, restylage des sélecteurs `.rdw-*`, test au contrat HTML
      à réécrire.
- [ ] **Les 197 avertissements eslint de Category**, en quatre lots. Commencer par les **22
      `react-hooks/exhaustive-deps`** : c'est le seul lot susceptible de cacher de vrais bugs. Puis
      109 `no-explicit-any` (typage de fond), 38 `await-thenable` et 20 `require-await` (cosmétique,
      concentrés dans les tests), 8 `no-console`.
- [ ] **`react-router` : open redirect** par antislash dans `<Link>` / `useNavigate`. Installé
      6.30.6, corrigé en **≥ 7.18.0** — à traiter comme une montée de version majeure.
- [ ] **`autoload.psr-0` → PSR-4.** Toujours présent en `{"": "src/"}`, un fourre-tout qui ralentit
      l'autoload et masque les erreurs de namespace.
- [ ] **`doctrine/annotations`** — contrainte `^1.14|^2.0` encore déclarée. **Pas une suppression
      gratuite** : un consommateur subsiste, `src/Oro/Bundle/SecurityBundle/Annotation/Loader/AclAnnotationLoader.php`.
      Le retirer suppose de migrer ce chargeur ACL.

## Palier 3 — à sonder avant de décider

Ces items n'ont pas de coût connu. La bonne première action est une **sonde jetable** dont le seul
produit est un nombre, pas une estimation.

- [ ] **Le trou de l'environnement `test`.** Les suites d'intégration ont le masquage de warnings PHP
      que #485 a fermé pour l'unitaire : après le premier boot de kernel, un warning est journalisé
      au lieu d'être levé. Surface bien plus large, **non mesurée** — la sonde exige MySQL et
      Elasticsearch en local.
- [ ] **Balayage exhaustif par fichier de la suite unitaire.** Le « 44/46 répertoires propres » est un
      **minorant** : dans les quatre répertoires `Acceptance`, le masquage reprend après leur premier
      boot de kernel. Une isolation par fichier le transformerait en inventaire.
- [ ] **Behat sur changement front seul.** #494 ignore Behat quand seuls des tests PHP changent, sans
      perte de couverture. Aller plus loin est une **décision de couverture** : Behat pilote l'UI, et
      la migration vers Playwright n'a transféré que 32 scénarios. À chiffrer en comparant ce que
      Playwright couvre réellement.
- [ ] **Les ~290 alertes npm restantes.** **Triage**, pas bascule de `yarn audit` en bloquant : 88 %
      sont des dépendances de développement, et un garde qui crie à ce taux se contourne au lieu de
      protéger.

## Parké — ne pas réouvrir sans raison neuve

Décidé lors de l'audit du 2026-09-24, toujours valable.

- Phases **C2/C3** (183 fichiers Backbone) et **D1** (482 `.js` non typés) — xl, aucune urgence.
- **Jest phases 4-7.**
- **Burn-down PHPStan** — la baseline décroît d'elle-même : 27 625 → 19 334 → 19 296.
- **#305** (panneau « Manage filters » en React) — fermée après ~9 cycles CI brûlés ; exige un point
  d'arrêt live ou une migration Playwright préalable des 15 usages de `I show the filter`.
- **`jquery.multiselect.less`** — le pied de page du add-select et le `FamilyFilter.tsx` de DQI
  écrivent à la main des `ui-multiselect-*`.

## Notes techniques

- **PHP uniquement dans Docker** : `docker-compose run --rm php php <cmd>` ; l'hôte est en 8.1.
- **Tâches via Castor**, pas `make` : le Makefile a été supprimé (#128). `castor list` les énumère.
- **Jamais Jest en local** — substituts : sonde plain-node + jsdom, `ts.transpileModule`,
  `enhanced-resolve`. Voir `tests/front/WRITING-FRONT-TESTS.md`.
- **CI** : GitHub Actions sur runners auto-hébergés partageant **un seul démon Docker**. Un shard
  Behat rouge est deux événements distincts — un scénario qui échoue (4-9 min) ou un job qui *pend*
  dans `Setup test database` (72 min) : tirer les **durées** par job avant d'ouvrir un log.
- **Une PR de docs seule ne peut pas fusionner** : elle matche `paths-ignore`, aucun check n'est
  rapporté, `ci-success` n'arrive jamais. Ce fichier doit voyager dans une PR de code.
- **Architecture** : hexagonal (`back/Application|Domain|Infrastructure`) et legacy
  (`Component/Bundle`) coexistent.
- **Licence** : OSL 3.0 / GPL.

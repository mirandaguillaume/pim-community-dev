# Roadmap — Akeneo PIM Community Edition Fork

**Dernière révision : 2026-10-20.** Le dépôt en est à la PR #501. La version du 2026-02-20 n'avait
jamais été relue : elle annonçait comme « prochaine étape majeure » un upgrade Symfony livré depuis,
et listait en cours des PR numérotées #11 à #18. Chaque état ci-dessous a été mesuré dans l'arbre,
avec le chiffre qui le fonde, pour qu'un lecteur puisse le réfuter en une commande plutôt que d'y
croire.

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
| Avis de sécurité | **0 critique** sur les trois arbres npm et `composer.lock` ; les 17 derniers sont partis avec le harnais CRA du projet d'exemple du DSM |

## Ce que l'ancienne feuille de route annonçait

- **Phase 1 (dépendances)** — terminée. Les six PR « en cours » ont été résolues ou remplacées.
- **Phase 2 (Symfony 5.4 → 6.4 LTS)** — **faite**, et monolog 3 n'est plus bloqué.
- **Phase 3** — partielle : `doctrine/cache` et `symfony/security-guard` ont disparu, les annotations
  Doctrine ont été migrées en attributs dès le 2026-02-24, et `autoload.psr-0` a disparu (voir
  « Livré »). Reste le paquet `doctrine/annotations` (voir Palier 2).
- **Phase 4** — React 17 → 18 faite. Le reste n'a pas bougé.

---

Les paliers ci-dessous classent les chantiers par **état de mesure**, pas par taille : ce qui est prêt
à livrer, ce dont on connaît le coût, et ce qu'il faut sonder avant de décider.

## Affirmations non confirmées

Deux choses livrées reposent sur une explication **plausible mais non établie**. Elles sont ici parce
qu'une feuille de route qui ne distingue pas ce qu'elle sait de ce qu'elle suppose se périme en
silence.

- **Le mécanisme du `lib/` périmé (#499) n'est pas confirmé.** Ce qui est *prouvé* : l'artefact
  `front-packages-lib` du run 39253565744 contenait le bouton `embedded` trois jours après son
  retrait — téléchargé et lu, pas déduit. Ce qui reste *supposé* : que la cause soit les
  `*.tsbuildinfo` gitignorés survivant au `clean: false`. Le run de #499 n'a trouvé **aucun** état
  résiduel (son `find -print -delete` n'a rien imprimé), et l'argument chronométrique que j'avais
  avancé — « `dsm:build` en 1,9 s donc no-op » — s'est révélé faible : un build complet des onze
  workspaces prend 20,2 s sur le runner, contre 21,6 s pour le run supposé no-op. **Ce qui
  confirmerait :** un run où l'étape de purge imprime des fichiers. À guetter, pas à supposer.

## Palier 1 — prêt, borné, vérifié

**Vide.** Tout ce qui était à la fois mesuré, borné et sans risque a été livré (voir « Livré » plus
bas). Les items restants demandent soit une décision, soit une sonde préalable.

## Palier 2 — coût connu, chantier réel

- [ ] **Remplacer la chaîne draft-js** — 11 des 13 chemins d'alerte runtime restants, dont le seul
      XSS et le seul paquet sans correctif publié (`react-draft-wysiwyg`, non maintenu). #486 a déjà
      rendu le vecteur inatteignable en retirant les boutons `embedded` et `image`, donc l'urgence est
      retombée : il reste la dette et le défaut de valeur non contrôlée que son consommateur
      contourne par une `key` composite. Coût : nouvelle dépendance, restylage des sélecteurs
      `.rdw-*`, test au contrat HTML à réécrire.
- [ ] **Les 171 avertissements eslint de Category**, en quatre lots. 197 au départ, descendus en deux
      coups **sans toucher à un seul tableau de dépendances risqué** : #500 a traité les 5
      `exhaustive-deps` dont la dépendance manquante est un singleton vérifié (no-op d'exécution), et
      la suppression de `feature/legacy/` (#501) en a emporté 5 autres, 21 avertissements au total.
      Reste : **12 `react-hooks/exhaustive-deps`**, le seul lot susceptible de cacher de vrais bugs,
      scindé en 7 valeurs d'état omises (vrais candidats à une fermeture périmée) et 5 callbacks non
      mémoïsés — les ajouter tels quels produit une boucle de re-render infinie, il faut d'abord un
      `useCallback`. Puis 96 `no-explicit-any` (typage de fond), 36 `await-thenable` et 19
      `require-await` (cosmétique, concentrés dans les tests), 8 `no-console`.
      **Aucun de ces 12 n'est validable en local** : Jest est interdit sur cette machine et une
      boucle de render ne se verrait pas dans un test unitaire.
- [ ] **Le build publié du DSM n'est consommable qu'à l'intérieur d'un bundler configuré.**
      Découvert en essayant de remplacer le serveur de dev du projet d'exemple par Vite. Deux causes
      mesurées, indépendantes :
      1. **`lib/index.js` n'expose aucun export nommé analysable statiquement.** C'est un barrel de
         dix `__exportStar(require(...), exports)`, qui copient les propriétés par `for..in` **à
         l'exécution**. webpack, rspack et Jest résolvent ces noms à l'exécution et fonctionnent ;
         rollup et esbuild analysent statiquement et ne voient rien, donc
         `import {Badge} from 'akeneo-design-system'` échoue au build
         (`"pimTheme" is not exported by "../lib/index.js"`).
      2. **`lib/` référence des assets hors du paquet et ne les embarque pas.** `tsc` recopie tel
         quel le `require("../../static/illustrations/AddingValue.svg")` du source ; depuis
         `node_modules/akeneo-design-system/illustrations/`, ce chemin résout vers
         `node_modules/static/`, qui n'existe pas. **0 fichier `.svg` dans `lib/`.**
      Le PIM n'en souffre pas : rspack intercepte les imports d'assets par `module.rules` et résout
      les noms CJS à l'exécution. Mais aucun consommateur ne peut charger ce paquet sans le même
      filet — ce qui vaut aussi pour `vite`, `rollup`, Node nu, et donc pour toute sonde locale.
      Le corriger est un chantier sur le build du DSM (exports explicites + assets copiés dans
      `lib/`), pas sur ses consommateurs.
- [ ] **`react-router` : open redirect** par antislash dans `<Link>` / `useNavigate`. Installé
      6.30.6, corrigé en **≥ 7.18.0** — à traiter comme une montée de version majeure.
- [ ] **`css-loader` 3 → 7.** Seule voie vers le correctif de `postcss-selector-parser` à la racine :
      il y est en **6.0.6**, vulnérable à `GHSA-rj75-hqrm-r3gf`, corrigé en **≥ 7.1.6** — que ses deux
      consommateurs refusent (`postcss-modules-local-by-default` exige `^6.0.2`, `postcss-modules-scope`
      `^6.0.0`). Forcer la majeure par `resolutions` toucherait la compilation des CSS modules de tout
      le front ; l'avis s'exécute au build sur nos propres feuilles de style, donc rien ne presse.
      Corriger au passage sa déclaration en `dependencies` alors que c'est un loader de build.
- [ ] **`doctrine/annotations`** — contrainte `^1.14|^2.0` encore déclarée. **Pas une suppression
      gratuite** : un consommateur subsiste, `src/Oro/Bundle/SecurityBundle/Annotation/Loader/AclAnnotationLoader.php`.
      Le retirer suppose de migrer ce chargeur ACL.

## Palier 3 — à sonder avant de décider

Ces items n'ont pas de coût connu. La bonne première action est une **sonde jetable** dont le seul
produit est un nombre, pas une estimation. Deux exemples qui ont marché : une config eslint posée pour
être jetée a cadré le chantier Category en trente secondes (496 erreurs), et un balayage de la suite
unitaire par processus frais a borné un risque qu'on croyait inconnu (44/46 propres).

- [ ] **Le trou de l'environnement `test`.** Les suites d'intégration ont le masquage de warnings PHP
      que #485 a fermé pour l'unitaire : après le premier boot de kernel, un warning est journalisé
      au lieu d'être levé. Surface bien plus large, **non mesurée** — la sonde exige MySQL et
      Elasticsearch en local.
- [x] **Balayage exhaustif par fichier de la suite unitaire — RETIRÉ, l'item demandait la mauvaise
      mesure.** Une extension PHPUnit lisant `thrownErrors` après chaque test rapporte
      `NO_SYMFONY_HANDLER` pour **les 7054 tests** des onze suites unitaires et d'acceptance. La
      cause est l'empilement : PHPUnit installe son gestionnaire avant le test, un boot de kernel
      pose celui de Symfony **par-dessus**, et à `Test\Finished` PHPUnit a déjà dépilé. L'état qui
      décide « levée ou journalisée » n'existe qu'**à l'intérieur du corps du test**, à l'instant du
      warning : aucun instantané par test ne peut l'observer, et une isolation par fichier ne dirait
      que *quel fichier* dégrade le gestionnaire, pas *quels tests* ont tourné dégradés.
      C'est pourquoi #485 mesurait un **effet** et non un état, et c'est ce que fait désormais le
      canari de #504. Le mécanisme était déjà documenté, plus précisément que ma conclusion, dans le
      commentaire de `config/bootstrap.php`.
- [ ] **Behat sur changement front seul.** Aller plus loin que #494 est une **décision de couverture** :
      Behat pilote l'UI, et la migration vers Playwright n'a transféré que 32 scénarios. À chiffrer en
      comparant ce que Playwright couvre réellement.
- [ ] **Les ~240 alertes npm non critiques.** **Triage**, pas bascule de `yarn audit` en bloquant :
      l'essentiel est en dépendances de développement, et un garde qui crie à ce taux se contourne
      au lieu de protéger.

## Livré — les leçons valent plus que les lignes

Série #489 → #501, 13 PR. Conservé ici pour ce qui se réutilise, pas pour le palmarès.

- **Trois gardes posés** : les warnings PHP sont fatals dans la suite unitaire (#485,
  `framework.php_errors.throw` — et `var/cache/test_fake` doit être vidé après l'édition, sinon le
  conteneur compilé non revalidé rend le changement sans effet) ; Category a une config eslint
  appliquée depuis `lint:check` (#489/#500) ; le bouton XSS du texte riche est épinglé par un test que
  la CI exécute vraiment (#486).
- **Un garde retiré** : Stryker mutait `front-packages/` dont son exécuteur ignore les tests, ce qui
  ne pouvait produire qu'un score de 0 (#486).
- **Zéro avis critique** sur `yarn.lock` et celui du DSM — `handlebars` (#493) et `websocket-driver`
  (#498). **Portée à ne pas élargir de tête** : un quatrième lockfile existe, celui du projet
  d'exemple du DSM, et il en porte 17 (voir Palier 3).
  **Dependabot ne corrige qu'un lockfile** : le DSM a le sien, et l'asymétrie va dans les deux sens.
  Vérifier les deux, puis se demander *qui fournit l'entrée* et *quel correctif cet arbre accepte* —
  trois paquets, trois réponses différentes.
- **~4900 lignes de code mort** : `es/` orphelin (816 fichiers), `dependency-cruiser` (mort depuis
  3 ans et demi, il épinglait handlebars 4.7.7 et enracinait 17 chemins vulnérables), six
  devDependencies, et `feature/legacy/` de Category (37 fichiers, inatteignable depuis le flag retiré
  le 2023-03-13). **Avant de corriger du code, vérifier qu'il est exécuté** : auditer les 5
  `exhaustive-deps` du dossier legacy aurait été du travail entièrement perdu.
- **La discipline qui a tout structuré** : refuser un signal que l'échec produirait à l'identique.
  `EXIT=0` après un pipe, un linter qui félicite un ensemble vide via `.prettierignore`, un conteneur
  compilé non revalidé, une suite qui journalise au lieu de lever, `chown` sans `-h` sur des liens
  symboliques, `gh pr list --limit` qui tronque en silence, un motif `!` sans
  `predicate-quantifier: some-with-excludes`, un test vert parce qu'un autre a muté un handler global.
  La parade : exiger la **présence** de ce qu'on attend, puis le faire échouer exprès pour voir si on
  le remarque.
- **`autoload.psr-0` → PSR-4 préfixé** : le fourre-tout `{"": "src/"}` est remplacé par quatre
  règles explicites (`Akeneo\`, `Oro\`, `Acme\`, `Behat\`) plus une entrée `classmap`.
  **La justification que cette feuille de route avançait était fausse** : « un fourre-tout qui
  ralentit l'autoload » ne résiste pas à la mesure — 187 ms contre 185 ms sur 10 boots de kernel
  chacun, soit 1 %, dans le bruit. Un répertoire de repli n'est consulté qu'après l'échec de toutes
  les règles préfixées, donc il ne coûte qu'un `stat` sur les classes qui n'existent nulle part.
  Le gain réel est une **contrainte** : les 4 namespaces racines deviennent explicites, et tout
  ajout non déclaré échoue bruyamment au lieu de marcher en silence. S'y ajoutent la disparition
  d'un avertissement que `composer validate` émettait déjà sur `psr-0`, et l'abandon de la
  traduction des tirets bas propre à PSR-0 — inutilisée ici, 0 sur 5641 déclarations.
  **La surprise mesurée** : le fourre-tout n'était porteur que pour **une seule** classe, le
  `Kernel` du namespace global à `src/Kernel.php`, qu'aucune règle préfixée ne peut atteindre.
  Retirer le fourre-tout sans le voir fait tomber le boot sur un `ClassNotFoundError`, et le
  classmap passe de 23 318 à 23 317 classes — un compte à une unité près qui nomme le coupable.
  `autoload-dev.psr-0` est laissé tel quel, délibérément : son préfixe `Context` n'est **pas**
  interchangeable, PSR-0 incluant le préfixe dans le chemin là où PSR-4 le retire.
  **La preuve de neutralité est un contrôle, pas un raisonnement** : la même sonde, appliquée aux
  deux configurations, résout 414 classes réelles extraites des fichiers de `src/` et rend un
  résultat identique au caractère près — 39 non résolues des deux côtés, les mêmes, toutes des
  classes de test au namespace décorrélé du chemin, que PHPUnit charge par chemin de fichier.
- **Les 17 derniers avis critiques, et le harnais qui les enracinait.** Le projet d'exemple du DSM
  tournait sur **Create React App 3.4.3**, dont webpack 4 traîne tout le passif transitif. Remplacé
  par un harnais Jest 29 + React 18 aligné sur les pairs que le DSM déclare (`^18.3.1`). Mesuré avec
  le même outil avant/après — `yarn audit`, qui compte des **chemins de résolution** là où GitHub
  compte des **alertes**, d'où l'écart d'échelle :

  | | avant | après |
  |---|---|---|
  | chemins critiques | 66 | **0** |
  | total | 891 | **20** |
  | entrées de lockfile | 1427 | **527** |

  **Quatre incohérences avaient la même racine** : React 17 installé contre une plage de pairs
  `^18.3.1` ; un `src/index.js` appelant `createRoot`, API de React **18**, absente de sa propre
  dépendance ; un lockfile qui ne contenait **aucune** entrée pour `react@^17.0.2` mais une pour
  `react@^16.13.1` ; et `--openssl-legacy-provider` sur chaque script, contournement du md4 de
  webpack 4 que Node 17+ a retiré. Toutes partent avec CRA.
  **Pourquoi personne ne l'a vu** : la CI ne lance que `yarn test`, qui importe `App` directement et
  **jamais le point d'entrée navigateur**. Et le `.env` contenait `SKIP_PREFLIGHT_CHECK=true`, soit
  le garde de cohérence des versions de CRA **désactivé à la main** — précisément celui qui aurait
  signalé le lockfile React 16.
  **Le snapshot a été remplacé par des assertions explicites.** Il épinglait
  `class="sc-jSFkmK jjMYxm"`, des hachages styled-components qui changent à la moindre édition de
  style : il échouait pour des raisons muettes sur la seule question qui compte ici, le build est-il
  utilisable. Les trois assertions qui le remplacent ont été validées **sans Jest** (interdit en
  local) par une sonde plain-node + jsdom reproduisant le `moduleNameMapper`.
  **La cause structurelle, à retenir** : ni le DSM ni son projet d'exemple ne figurent dans les 21
  workspaces de la racine. Ce sont **trois arbres npm indépendants**, et les 29 `resolutions` de la
  racine n'en atteignent qu'un. C'est pour ça que #493 et #498 ont dû patcher le DSM séparément, et
  que l'exemple est passé entre les mailles jusqu'ici.
- **Le troisième garde désarmé de la série.** `phpunit.xml.dist` porte `failOnRisky="false"`, alors
  que PHPUnit 10 active `beStrictAboutTestsThatDoNotTestAnything` **par défaut** : un test qui
  n'exécute aucune assertion est détecté, rapporté… et n'échoue pas. Mesuré sur les **8** fichiers de
  configuration PHPUnit du dépôt : **7 sur 8 l'ignorent**, et `Connectivity/Connection` est le seul à
  l'armer — un précédent interne qui prouve que ça tient ici.
  **Preuve que le garde tire**, avec un test sans assertion ajouté exprès :

  | | `Risky` | code de sortie |
  |---|---|---|
  | avec `--fail-on-risky` | 1 — « This test did not perform any assertions » | **non nul** |
  | sans le drapeau (état du dépôt) | 1 — même détection | **0**, « OK, but there were issues! » |

  **Coût de l'armement : nul.** Sept suites mesurées à zéro risky — `PHPUnit_Unit_Test` (5719 tests),
  `Category_Unit_Test`, et les acceptance Channel, Job, Installer, Measurement, ImportExport.
  Le drapeau est posé en **CLI sur les tâches Castor** plutôt qu'en attribut XML, délibérément : les
  fichiers de configuration sont partagés avec les suites d'intégration, que je ne peux pas compter
  sans MySQL ni Elasticsearch. Étendre l'armement à celles-là demande d'abord cette mesure.
  À noter : `--fail-on-risky` attrape les tests à **zéro assertion**, pas les tautologies. Il reste
  5 `assertTrue(true)` dans 3 fichiers, dont 2 où c'est l'idiome « aucune violation levée ».
- **Le gain de #494 est confirmé, par mesure.** `test-behat` est rapporté **`SKIPPED`** sur #504, la
  première PR au profil voulu — un diff limité à un `*Test.php`, donc `backend` vrai (les jobs
  PHPUnit gardent la fusion) et `backend_prod` faux. L'affirmation sort de la liste des non
  confirmées. Il reste à mesurer le gain sur une PR ne touchant que `tests/**`.
- **Le quatrième garde désarmé, et le pire des quatre.** Le job `lint-back` lançait
  `php-cs-fixer fix` — en mode **écriture**, pas en dry-run — sur
  `components/identifier-generator/back/tests/.php_cs.php`, **juste avant** l'étape qui exécute
  `castor test:lint-back` → `\identifier_generator\lintBack()` → `\csFixer(ce même fichier)`, soit
  le dry-run sur la configuration qu'on venait de corriger. Les corrections mouraient avec le runner,
  donc la violation restait dans git indéfiniment pendant que la CI restait verte.
  Ce n'est pas un drapeau qui *ignore* un résultat comme les trois autres : c'est la CI qui
  **réécrit l'arbre de travail** pour que le contrôle ne puisse pas échouer.
  **Mécanisme prouvé par expérience**, dans un espace inscriptible — violation introduite exprès :
  `Found 1 of 286` → l'étape de la CI → `Fixed 1 of 286` → le contrôle suivant → `Found 0 of 286`.
  **Retrait gratuit, mesuré** : 0 violation sur les 286 fichiers aujourd'hui, cache désactivé.
  Deux pièges rencontrés en chemin, qui valent plus que le correctif :
  - mon premier dry-run annonçait « 0 of 286 » **servi par un cache** de 123 Ko sans rien examiner.
    C'est exactement ce que garde l'étape voisine, « Clean stale CS-fixer cache » — un vrai garde,
    correctement posé, dans le même bloc de six lignes que celui qui était neutralisé.
  - ma première expérience a *échoué à reproduire* le mécanisme, parce qu'en local le conteneur php
    tourne en `www-data` (uid 1000) alors que les fichiers sont à l'uid 1001 : `IOException, not
    writable`. La CI, elle, fait `sudo chown -R $(id -u):$(id -g) .` avant. **Un échec d'expérience
    dû à l'environnement ressemble à une réfutation** — le log de CI (`Fixed 0 of 286`, sans
    IOException) a tranché.
  C'est aussi le seul cas du dépôt : aucun autre `fix` sans `--dry-run` dans `.github/workflows/`.
- **Séquencement** : ne jamais empiler une PR sur une branche qui fusionnera en **squash** — le SHA
  est réécrit et la branche empilée devient divergente (#495 l'a payé). Brancher sur `master` et
  accepter de régénérer le lockfile après la fusion de la base.

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
- **`yarn install` en local** : `-e HOME=/tmp -e YARN_CACHE_FOLDER=/tmp/yarn-cache`, et purger les
  fichiers root-owned avec `find . -path "*/node_modules/*" -uid 0 -exec chown -h 1001:1001 {} +`.
  Le `-h` est indispensable : sans lui, `chown` suit les liens et `node_modules/.bin` n'est quasiment
  que des liens.
- **CI** : GitHub Actions sur runners auto-hébergés partageant **un seul démon Docker**. Un shard
  Behat rouge est deux événements distincts — un scénario qui échoue (4-9 min) ou un job qui *pend*
  dans `Setup test database` (72 min) : tirer les **durées** par job avant d'ouvrir un log.
- **`.github/workflows/` contient 8 fichiers.** Lire `ci.yml` seul mène à des conclusions fausses :
  `dsm-test.yml` exécute la suite du design system, que `ci.yml` ignore.
- **Une PR de docs seule ne peut pas fusionner** : elle matche `paths-ignore`, aucun check n'est
  rapporté, `ci-success` n'arrive jamais. Ce fichier doit voyager dans une PR de code.
- **Architecture** : hexagonal (`back/Application|Domain|Infrastructure`) et legacy
  (`Component/Bundle`) coexistent.
- **Licence** : OSL 3.0 / GPL.

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
- **`@ts-ignore` → `@ts-expect-error`, mais seulement les 9 qui comptent.** `@ts-ignore` est une
  suppression **sans expiration** : elle masque encore après la disparition de l'erreur, et rien ne
  le signale. `@ts-expect-error` fait l'inverse — `error TS2578: Unused '@ts-expect-error'
  directive.` casse le build, prouvé en ajoutant une directive superflue (RED), puis build complet
  vert après retrait (GREEN).
  **La mesure a réduit le chantier de 91 sites à 9**, et c'est elle qui vaut le détour :

  | position des 91 `@ts-ignore` | sites |
  |---|---|
  | fichiers de test — que rien ne type-checke | **58** |
  | production, **hors** de tout type-checker | **24** |
  | production, dans un workspace qui construit | **9** |

  Convertir les 82 autres serait **exactement aussi inerte** que les laisser : un
  `@ts-expect-error` que personne ne vérifie ne s'expire jamais.
  **8 converties sur 9, et la neuvième est la vraie leçon.** `RichTextEditor.tsx:41` est
  délibérément resté en `@ts-ignore` : la CI y rapporte `TS2578: Unused '@ts-expect-error'
  directive` — deux runs, deux workflows, d'accord entre eux — alors qu'un build local complet
  déclare la directive nécessaire. La présence de l'erreur dépend du `@types/react-draft-wysiwyg`
  que l'arbre résout, et la CI n'installe que la racine (`yarn install --frozen-lockfile`) là où un
  développeur a aussi le `node_modules` propre du DSM.
  **`@ts-expect-error` exige que l'erreur soit déterministe d'un environnement à l'autre** ; là où
  elle ne l'est pas, il transforme une différence d'installation en échec de build. `@ts-ignore` y
  est le choix correct, pas le choix paresseux — et c'est écrit dans le fichier.
  Les 8 autres sont vérifiées par le build complet de leur workspace (DSM, `front-packages/shared`,
  Category), buildinfo purgé, **et aucune n'était morte**.
  Au passage : la cause que le commentaire d'origine invoquait — un conflit `@types/react` 17 vs 18
  — n'est **plus reproductible**, il n'y a qu'un seul `@types/react` (18.3.28) partout, la racine le
  résolvant en `^18.3.0`.
- [ ] **Il n'existe aucun type-check à l'échelle du dépôt**, et c'est la vraie trouvaille du point
      ci-dessus. Mesuré : le `tsconfig.json` de la racine n'inclut que `public/bundles` et
      `frontend/types.d.ts` — la **sortie de build**, pas `src/` ; aucun script `type-check` ;
      aucun job de CI ne lance `tsc` globalement. Le type-checking n'existe que par workspace, et
      **11 des 22 workspaces n'ont aucun script `tsc`** — dont
      `Pim/Automation/DataQualityInsights/front`, qui n'a même pas de `tsconfig`. Cela représente
      **plus de 1000 fichiers `.ts`/`.tsx`** qu'aucun vérificateur de types ne lit : rspack
      transpile en dépouillant les types sans les contrôler, comme swc et ts-jest.
      **Piège de mesure à connaître avant de chiffrer ce chantier** : un `tsc --noEmit` naïf sur un
      de ces workspaces rapporterait surtout du bruit de résolution de modules (les alias `pim/…`,
      `oro/…` que rspack résout et que tsc ignore), pas des erreurs de type. Dimensionner cet item
      suppose donc d'abord une config qui reproduit la résolution de rspack — la sonde est elle-même
      un chantier, et c'est pour ça qu'il reste au Palier 3.
- **Dependabot corrige le DSM et laisse la racine derrière**, et c'est systématique, pas anecdotique.
  Mesuré le 2026-10-21 sur les 76 alertes ouvertes : **46 dans `yarn.lock` contre 28 dans celui du
  DSM**, **14 paquets alertés uniquement à la racine**, 11 dans plusieurs manifestes — et les PR
  Dependabot récentes (#490, #491, #492, #496, #497, #508) portent **toutes** la mention
  « in /front-packages/akeneo-design-system ».
  La cause est structurelle : à la racine ces paquets sont purement **transitifs**, donc il n'existe
  aucune dépendance directe à bumper et Dependabot ne peut rien proposer. Le seul levier est
  `resolutions`, que ce dépôt utilise déjà 30 fois. `follow-redirects` en est le cas traité :
  #508 a fermé les 2 alertes du DSM, la résolution racine `^1.16.1` les 2 de la racine
  (`GHSA-q3jh-wrc6-jgq8` et `GHSA-8r9p-f939-6h3c`, deux fuites d'en-têtes d'identifiants).
  **Il en reste 10 du même profil** — `@babel/core`, `brace-expansion`, `immutable`, `js-yaml`,
  `linkify-it`, `node-fetch`… — chacun à vérifier manifeste par manifeste avant d'y toucher.
- [ ] **Deux `composer.json` vestigiaux produisent une alerte fantôme.**
      `src/Akeneo/Tool/Bundle/StorageUtilsBundle/composer.json` et
      `src/Akeneo/Tool/Component/StorageUtils/composer.json` déclarent `php: >=7.1.0` et
      `symfony/http-kernel: ^2.3` — vestiges de l'époque où ces bundles étaient publiés séparément.
      Le dépôt tourne en **PHP 8.4 / Symfony 6.4**, la racine déclare **0 `repositories`** et
      **`replace` les deux paquets**, donc ces manifestes ne gouvernent aucune installation.
      Mais **Dependabot les lit** : d'où `GHSA-h7vf-5wrv-9fhv` (medium, « Symfony storing cookie
      headers in HttpCache ») sur un `symfony/http-kernel >= 2.3, < 3.0` qui n'est installé nulle
      part. C'est un **faux positif produit par un artefact périmé** — même famille que `es/`,
      `dependency-cruiser` ou le `lib/` périmé. Les supprimer ou aligner leurs contraintes est une
      décision à prendre, pas un geste à glisser dans une PR de sécurité.
- **`docker/Dockerfile_node` supprimé : 53 lignes que rien ne construisait.** Le service `node` de
  `docker-compose.yml` utilise l'image publique **`node:22`** ; les deux seules références au fichier
  étaient dans des documents de plan de février 2026 qui projetaient de le migrer en bookworm — plan
  dépassé par le passage à l'image publique, fichier laissé derrière.
  Il emportait trois défauts au-delà de l'inutilité : `node_22.x` sur **bullseye** (Debian 11), trois
  `apt-key add` dépréciés, et un binaire `dumb-init` récupéré par `ADD` **sans somme de contrôle**.
  Au passage, `deb.nodesource.com/node_22.x/dists/bullseye/Release` répond **404** — et bookworm
  aussi, ce qui indique un changement de disposition chez nodesource plutôt qu'un simple abandon de
  bullseye. Donc le fichier ne pouvait probablement plus se construire ; mais la raison de la
  suppression reste que **rien ne le référence**, l'inconstructibilité n'étant qu'un corollaire.
  **Ce n'est PAS une remédiation de CVE** et ne doit pas être comptée comme telle : supprimer une
  base EOL que personne ne construit ne ferme aucune exposition.
- **Trois paquets `high` corrigés sans aucune `resolutions` : les lockfiles étaient juste périmés.**
  `brace-expansion`, `js-yaml` et `ws` étaient alertés dans la racine ET le DSM. Toutes leurs specs
  sont des **plages caret**, et les versions corrigées étaient **déjà autorisées** par ces plages —
  le lockfile de l'exemple du DSM le prouvait à lui seul, portant déjà `brace-expansion 1.1.21`,
  `js-yaml 3.15.2` et `ws 8.22.0` pour les mêmes specs. Supprimer les entrées et réinstaller a
  suffi :

  | paquet | plancher requis | obtenu |
  |---|---|---|
  | `brace-expansion` 1.x / 2.x | 1.1.21 / 2.1.7 | 1.1.21 / 2.1.7 |
  | `js-yaml` 3.x / 4.x | 3.15.2 / 4.3.2 | 3.15.2 / 4.3.2 |
  | `ws` 7.x / 8.x | 7.5.11 / 8.21.0 | 7.5.13 / 8.22.0 |

  **Ce que ça évite** : le workflow d'analyse proposait des `resolutions` **à clés ordonnées**, en
  s'appuyant sur une lecture du source de yarn 1.22.22 (les résolutions sont premier-match-gagnant
  dans l'ordre des clés du `package.json`). C'était exact techniquement et **inutile** : zéro
  résolution à maintenir, zéro violation sémantique, zéro ordre signifiant à préserver.
  **La règle qui se dégage** : avant de choisir la forme d'un correctif de dépendance, mesurer les
  **specs réellement verrouillées**, pas la famille de l'alerte. `immutable` a exigé un épinglage
  *hors* plage déclarée ; ces trois-là n'exigeaient rien du tout. Même inventaire, formes opposées.
  Diff **confiné et vérifié** : six lignes `version` à la racine, celles visées, aucune cascade sur
  les voisins. Audit : 0 chemin pour les trois dans les deux arbres. `category:lint:check` inchangé
  (0 erreur, 171 avertissements) et build DSM vert — ces paquets vivent dans la chaîne d'outils
  (`brace-expansion` → `minimatch` → globbing d'eslint/glob/Stryker, `ws` → serveurs de dev), donc
  le vrai juge reste les jobs de lint et de test.
- **La chaîne draft-js est remplacée par Tiptap : 14 des 15 alertes de la chaîne se ferment.**
  `react-draft-wysiwyg` portait un XSS **sans correctif publié** — npm annonce la plage corrigée
  comme `<0.0.0`, sa façon de dire que le paquet n'est plus maintenu. Un remplacement était donc la
  seule issue, et la surface s'est révélée minuscule : draft-js n'était importé que par **2
  fichiers**, tous deux dans le DSM.

  | paquet | alertes | sort |
  |---|---|---|
  | `immutable` | 6 | fermées |
  | `linkify-it` | 4 | fermées |
  | `react-draft-wysiwyg` | 2 | fermées |
  | `ua-parser-js` | 1 | fermée |
  | `node-fetch` | 2 | 1 fermée (DSM), **1 reste** |

  La survivante est `node-fetch@2.6.1` à la racine, qui n'a rien à voir avec draft-js : elle arrive
  par `jest-fetch-mock` → `cross-fetch`. L'avis est unique, compté une fois par manifeste.
  **Annoncer 15 aurait été faux d'une unité.**

  **Ce qui a rendu l'échange possible** : la valeur stockée était déjà du **HTML** (`draftToHtml` en
  sortie, `htmlToDraft` en entrée), pas le JSON brut de draft-js — donc aucune migration de données,
  et le HTML est le format **natif** de Tiptap. C'est ce critère, et non la notoriété, qui a écarté
  Lexical : successeur légitime de draft-js mais encore en `0.x`, et il exige `@lexical/html` plus
  une sérialisation à écrire — soit exactement la couche de conversion qu'on démontait.

  **Deux gains qui n'étaient pas l'objectif** :
  1. le consommateur dans Category forçait un remontage par une **`key` composite**, avec un
     commentaire l'avouant : *« the RichTextEditor in the DSM is not able of considering a changed
     value »*. L'ancien composant n'initialisait qu'une fois. La `key` est supprimée, remplacée par
     une vraie synchronisation contrôlée ;
  2. **6 `transformIgnorePatterns`** demandaient à Jest de transpiler `react-draft-wysiwyg` dans six
     workspaces. Tiptap livre du CommonJS (`main` → `.cjs`, et les 13 sous-chemins de `@tiptap/pm`
     ont tous une condition `require`), donc l'exception est inutile : supprimée partout. Une
     dépendance ne coûte pas que ses alertes.

  **Le signalement de sécurité sur mon propre code, réfuté par mesure** : une revue automatique a
  marqué `setLink({href})` comme vecteur XSS. L'extension `Link` de Tiptap filtre déjà les
  protocoles via `isAllowedUri` — vérifié sur `javascript:`, `JaVaScRiPt:`, une variante avec espace
  en tête, `data:` et `vbscript:`, aucune ne pose de lien — **et sur trois chemins**, dont le HTML
  analysé depuis la valeur stockée, que valider dans le handler n'aurait pas couvert. Le correctif
  suggéré était même nuisible : sa liste blanche casse les URL relatives. Rien n'a été changé, mais
  **un test a été ajouté dans les deux fichiers**, car la protection est de la *configuration* :
  reconfigurer `Link` l'éteindrait en silence [[disarmed-guards-pattern]].

  **Méthode — trois fois la même erreur** : `str.replace()` en Python rend la chaîne inchangée quand
  le motif échoue, **sans lever d'erreur**, donc mes scripts ont annoncé des succès pour des éditions
  nulles — dont un commentaire décrivant une prop `key` supprimée. Parade : `re.subn` et vérifier le
  compte. Deux variantes du même piège : chercher les traces d'une dépendance avec les motifs qu'on a
  en tête (`rdw-`, `DraftJS`) rate `html-to-draftjs` et `ContentBlock` — c'est **eslint** qui a trouvé
  le `jest.mock` résiduel, et `git grep` qui a donné la mesure propre ; et `git diff --diff-filter=ACM`
  **ne voit pas les fichiers neufs**, donc la barre d'outils n'a pas été formatée. Enfin, formater avec
  une commande *équivalente en apparence* ne satisfait pas la vérification : Category lance
  `prettier --config .prettierrc.json --parser typescript`, et seul son propre `lint:fix` le contente.

  **Et la résolution d'#518 devient morte**, donc elle part ici : `draft-js` était le seul
  consommateur d'`immutable`, qui a disparu des deux arbres. Les deux PR étaient correctes à leur
  heure — #518 fermait 6 alertes le jour même pour une ligne, celle-ci supprime la raison d'être du
  paquet — mais laisser une `resolutions` qui épingle un paquet absent est exactement la
  « devDependency morte qui enracine des transitifs » déjà documentée [[dead-devdependencies-carry-cves]].
  Piège rencontré en la retirant : `git checkout <branche> -- package.json` prend le fichier
  **entier**, pas le diff, et a donc **annulé en silence** la résolution d'#518 — invisible dans le
  diff comme une suppression intentionnelle. Repartir de `origin/master --` puis rejouer son seul
  changement par script ; les 30 autres résolutions sont vérifiées présentes.

  Vérifié : sonde plain-node + jsdom **16/16** (Jest est interdit ici), dont le point porteur —
  `setContent(v, {emitUpdate: false})` ne déclenche pas `onUpdate`, alors que sans l'option il le
  déclenche, donc la garde est nécessaire — et **le chargement ne réécrit pas le HTML stocké**, donc
  pas de migration silencieuse. Build du DSM vert, lint du DSM vert, Category à **0 erreur / 171
  avertissements**, sa base inchangée. Les tests Jest eux-mêmes ne tournent qu'en CI.
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

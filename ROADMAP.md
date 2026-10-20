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
| Avis de sécurité | **17 critiques, toutes dans `front-packages/akeneo-design-system/example/yarn.lock`** ; 259 alertes au total |

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
- **Le gain de #494 n'est pas mesuré.** Behat ne tourne plus quand seuls des tests PHP changent, et
  l'économie attendue est de ~69 min de runner par PR de ce profil. Aucune PR livrée depuis ne
  remplissait le critère — toutes touchaient du front ou `.github/**` — donc l'effet réel reste à
  constater sur la première PR qui ne modifie que des `*Test.php`.

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
- [ ] **Balayage exhaustif par fichier de la suite unitaire.** Le « 44/46 répertoires propres » est un
      **minorant** : dans les quatre répertoires `Acceptance`, le masquage reprend après leur premier
      boot de kernel. Une isolation par fichier le transformerait en inventaire.
- [ ] **Behat sur changement front seul.** Aller plus loin que #494 est une **décision de couverture** :
      Behat pilote l'UI, et la migration vers Playwright n'a transféré que 32 scénarios. À chiffrer en
      comparant ce que Playwright couvre réellement.
- [ ] **Les 17 avis critiques du projet d'exemple du DSM.** Découverts en lisant la sortie de
      `git push`, qui annonçait 17 critiques quand je venais d'écrire « 0 critique ». Les deux
      affirmations sont compatibles et c'est le piège : **le dépôt versionne QUATRE lockfiles**
      — `composer.lock`, `yarn.lock`, celui du DSM et `front-packages/akeneo-design-system/example/yarn.lock`
      — et je n'en avais mesuré que deux. Les 17 critiques sont **toutes** dans le quatrième, et
      aucune ailleurs. C'est aussi ce qui rend **#498 incomplet** : `websocket-driver` y est encore
      vulnérable, épinglé à la racine et dans le DSM mais pas là.
      **Ce n'est pas du code mort** : `dsm-test.yml` installe ce projet et lance `yarn test` dessus
      — le DSM installé comme dépendance dans une vraie application, un test de consommation que
      ni Storybook ni les tests unitaires ne remplacent. 11 fichiers versionnés, dernier touché en
      mars 2026 (#75).
      La cause est sa chaîne d'outils : c'est une application **Create React App**, et `react-scripts`
      traîne tout le passif transitif (`loader-utils`, `@babel/traverse`, `minimist`, `shell-quote`,
      `elliptic`, `pbkdf2`, `sha.js`, `cipher-base`, `eventsource`, `proxy-addr`, `form-data`).
      Trois voies, par coût croissant : des `resolutions` dans son `package.json` ; remplacer le
      harnais CRA par Vite ; ou décider que ce test de consommation ne vaut plus son coût.
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

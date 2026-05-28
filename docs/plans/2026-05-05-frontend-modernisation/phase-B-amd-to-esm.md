# Phase B — AMD → ESM migration

Date : 2026-05-29
Périmètre : 488 fichiers `.js` AMD répartis sur `src/`
Objectif : éliminer `require.js` du bundle, permettre tree-shaking RSPack

---

## Inventaire B1 (audit terminé)

| Indicateur                          | Valeur    |
| ----------------------------------- | --------- |
| Fichiers `define(` `.js`            | **488**   |
| Cycles AMD détectés (madge)         | **0** ✅  |
| Configs `requirejs.yml` à respecter | **13**    |
| Appels `require(` runtime           | **1 406** |
| Fichiers utilisant `__moduleConfig` | **33**    |

### Top dossiers AMD

1. `UIBundle/Resources/public/js/` racine → 29
2. `PimDataGridBundle/.../datafilter/filter/` → 24
3. `UIBundle/.../form/common/` → 24
4. `PimDataGridBundle/.../datagrid/cell/` → 23
5. `UIBundle/.../product/form/` → 23
6. `UIBundle/.../controller/` → 16
7. `PimDataGridBundle/.../datagrid/` → 13
8. `UIBundle/.../remover/` → 13 (12 utilisent `__moduleConfig` — à reporter)
9. `PimDataGridBundle/.../datagrid/action/` → 12
10. `UIBundle/.../product/field/` → 12

### Outils installés

- `madge@8.0.0` — détection de cycles + graphe de deps
- `jscodeshift@17.3.0` — codemod AST-based pour les transformations

---

## ⚠️ Contrainte fondamentale (prouvée empiriquement) — cible = CJS, pas ESM pur

`rspack.config.js` active **`amd: {}`** (obligatoire tant que des `define()`
subsistent — sinon RSPack ne suit pas le graphe de deps AMD et le bundle est
quasi vide). Sous l'analyse AMD native, un consumer `define(['pim/foo'], fn)`
reçoit ce que `__webpack_require__('pim/foo')` retourne — **et RSPack n'applique
PAS l'interop `.default` aux deps d'un `define`**.

Repro minimal (`/tmp/amd-repro`, RSPack 1.7.9, `amd:{}` + babel preset-env) :

| Forme du module migré                 | Consumer AMD `define([..])`                                      | Consumer ESM `import` |
| ------------------------------------- | ---------------------------------------------------------------- | --------------------- |
| `export default X` (ESM pur)          | ❌ reçoit `{default, __esModule}` → `X.method` undefined         | ✅ interop OK         |
| `module.exports = X` (CJS)            | ✅ reçoit `X` direct                                             | ✅ interop OK         |
| `import` + `module.exports` (hybride) | 💥 erreur compilation `ES Modules may not assign module.exports` | 💥                    |

**Conclusion :** on ne peut PAS convertir un module en `export default` tant qu'un
seul consumer `define()` en dépend (il reçoit le namespace → casse). C'est ce qui a
mis à terre toute l'UI enrichment à la Wave 1 (fetcher-registry.js, resté AMD,
dépend de `pim/base-fetcher` migré en ESM). La cible incrémentale **doit être CJS**.

### Codemod : `frontend/codemods/amd-to-cjs.js`

Transforme :

```js
define(['jquery', 'underscore', 'pim/foo'], function ($, _, Foo) {
  return X;
});
```

en :

```js
function __pimInterop(m) {
  return m && m.__esModule ? m.default : m;
}
var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var Foo = __pimInterop(require('pim/foo'));

module.exports = X;
```

**Règles** :

- Dep avec param utilisé → `var Alias = __pimInterop(require('path'));`
- Dep sans param / param inutilisé → `require('path');` (side-effect, pas de binding → pas de no-unused-vars)
- `return X` top-level du factory → `module.exports = X` (les `return` imbriqués dans les méthodes sont préservés)
- Helper `__pimInterop` injecté une fois si ≥1 dep bindée — déballe `.default` des deps ESM/TS, no-op pour AMD/CJS. Indispensable car `require()` brut n'interope pas.
- Erreur explicite si shape non canonique (array + function/arrow expression)

### Deux fixes complémentaires de l'interop (les deux nécessaires)

1. **Sortie CJS du codemod** (ci-dessus) → fixe le chemin `define([..])` natif.
2. **`module-registry.js` unwrap `.default`** (`requirejs-utils.js#createModuleRegistry`)
   → fixe le chemin `require()`/`requireContext` runtime (lazy-load) pour les
   modules ESM. No-op pour CJS, mais correct et requis au flip final pur-ESM.

### Cas non gérés

| Pattern                             | Compteur          | Stratégie                                           |
| ----------------------------------- | ----------------- | --------------------------------------------------- |
| `__moduleConfig` global             | 33 fichiers       | Phase B2.5 — passer la config en argument / wrapper |
| `define()` sans dependency array    | ~3                | Migration manuelle, signalé par exception           |
| Multiple `define()` dans un fichier | ~0                | Skip + review manuelle                              |
| `require(['x'], cb)` runtime calls  | 1 406 occurrences | Phase B2.6 — codemod séparé                         |

---

## Ordre recommandé pour B2

1. **POC** ✅ — codemod CJS + registry fix validés ; `date-formatter.js` re-migré en CJS (le fichier qui cassait en ESM, avec ses 3 consumers AMD `date-filter`/`updated`/`attribute/date` inchangés) — build vert.
2. **Wave 1 — Fetchers** : `fetcher/` (8 fichiers) — à re-migrer en CJS
3. **Wave 2 — Form helpers** : `form/common/` (59 fichiers) — à re-migrer en CJS
4. **Wave 3 — Grid** : `grid/` (6 fichiers)
5. **Wave 4 — Datagrid cells/filters** : ~50 fichiers
6. **Wave 5 — Controllers** : `controller/` (14 fichiers)
7. **Phase B2.5 — `__moduleConfig` refactor** : 33 fichiers
8. **Phase B2.6 — runtime `require()` codemod** : 1 406 call sites
9. **Phase B3 — flip final ESM** : une fois tous les `define()` éliminés, retirer
   `amd: {}` + require.js, puis pass mécanique `require`/`module.exports` →
   `import`/`export default` pour le tree-shaking.

À chaque wave : run codemod CJS → `yarn webpack-dev` → `yarn eslint` → **smoke test runtime obligatoire** (Behat datagrid OU Playwright login+grille — le build seul ne détecte PAS les ruptures d'interop) → PR.

---

## Risques

| Risque                                                                | Probabilité | Mitigation                                                                       |
| --------------------------------------------------------------------- | ----------- | -------------------------------------------------------------------------------- |
| `import` ESM tree-shake un module dont AMD attendait les side effects | Moyenne     | Option A : conserver les bare imports `import 'path';` pour les deps non-bindées |
| `__moduleConfig` non disponible casse 33 fichiers                     | Haute       | Phase B2.5 dédiée avant de migrer ces fichiers                                   |
| Cycle de deps introduit en ESM (vs AMD résilient)                     | Faible      | madge confirme 0 cycle actuel — surveiller à chaque wave                         |
| Performance bundle change                                             | Faible      | Bundle Analyzer avant/après chaque wave                                          |

---

## Critère de sortie Phase B

- `grep -r "^define(" src/` → 0 résultats
- `grep -r "requirejs(" src/` → 0 résultats
- `require.js` retiré de `public/js/`
- `yarn webpack-dev` passe
- Smoke test Playwright OK (login + grille produit + édition produit)

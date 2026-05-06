# Plan de modernisation du frontend Akeneo PIM

Date : 2026-05-05  
Périmètre : `src/` (front principal) + workspaces CRA éjectés  
Contrainte intégrateurs : **supprimée** — pas de bridge rétrocompatible, migration directe

---

## Contexte & chiffres clés

| Indicateur | État actuel |
|---|---|
| Couverture Jest (front principal) | ~54 % |
| Fichiers `.js` sans type | 822 |
| Fichiers Backbone/BaseForm | ~208 |
| Fichiers `define(` AMD/require.js | ~488 |
| Redux (store slices) | 19 fichiers |
| Appels react-query v3 | ~65 fichiers |
| Fichiers totaux front | ~2 100 |

---

## Phase 0 — Filet de sécurité (en cours)

**Objectif :** coverage Jest 54 % → 70 % avant toute migration structurelle.

**Pourquoi 70 % ?** En dessous, un refactoring Backbone → React peut casser des comportements silencieusement. 70 % couvre les chemins critiques sans immobiliser l'équipe sur des tests exhaustifs.

**Travail en cours :** phases Jest 1–30 (tests unitaires Connectivity Connection). Continuer jusqu'à atteindre 70 % sur les modules ciblés.

**Critère de sortie :** `yarn unit --coverage` → 70 % statements sur `src/`.

---

## Phase A — Modernisation des librairies (2–3 semaines)

Pas de changement structurel, remplacements 1-pour-1 à faible risque.

### A1 — Redux 4 → Redux Toolkit (RTK)

**Périmètre :** 19 fichiers Redux  
**Gain :** suppression de 60–70 % du boilerplate (actions creators, reducers switch/case), typage automatique avec `createSlice`.

**Migration :**
```
redux + react-redux  →  @reduxjs/toolkit + react-redux
createAction + switch  →  createSlice({ reducers: {} })
```

**Risque :** faible — RTK est rétrocompatible avec le store Redux existant.

### A2 — react-query v3 → v5

**Périmètre :** ~65 fichiers  
**Gain :** `useQuery` v5 unifie `isLoading`/`isFetching`, supprime `onSuccess`/`onError` (remplacés par `useEffect`), meilleure DX TypeScript.

**Breaking changes principaux :**
- `cacheTime` → `gcTime`
- `onSuccess`/`onError` dans `useQuery` → supprimés, migrer vers `useEffect`
- `QueryClient.invalidateQueries(key)` → `invalidateQueries({ queryKey: [key] })`

**Risque :** moyen — codemod disponible (`@tanstack/react-query-codemods`), mais vérifier les `onSuccess` patterns.

---

## Phase B — Supprimer AMD/require.js (3–4 semaines)

**Objectif :** éliminer les 488 fichiers `define(` AMD. Ce sont les "fondations" de Backbone — sans require.js, Backbone ne peut plus être chargé.

### B1 — Inventaire et dépendances circulaires

```bash
grep -r "define\(" src/ --include="*.js" -l | wc -l  # baseline
npx madge --circular src/                              # cycles à résoudre d'abord
```

### B2 — Conversion AMD → ESM

Stratégie : script de codemod + validation manuelle.

```js
// Avant (AMD)
define(['jquery', 'backbone'], function($, Backbone) { ... });

// Après (ESM)
import $ from 'jquery';
import Backbone from 'backbone';
```

**Outil :** `amd-to-esm` ou codemod custom (`jscodeshift`).

### B3 — Supprimer require.js du bundle

Une fois tous les `define(` convertis :
- Retirer `requirejs` de `package.json`
- Retirer le loader require.js de RSPack config
- Vérifier que le build passe

**Critère de sortie :** `grep -r "define(" src/` → 0 résultats.

---

## Phase C — Migration Backbone → React (Strangler Fig)

**Principe :** ne pas réécrire en bloc. Identifier les modules à plus forte valeur, les réécrire en React, désactiver les équivalents Backbone.

### C1 — Grille produit (Product Grid)

**Pourquoi en premier ?** La grille est le cœur de l'UX PIM. Elle est déjà partiellement en React (filtres, header). Les 208 fichiers Backbone incluent ~40 qui touchent la grille.

**Étapes :**
1. Identifier les `BaseForm` qui alimentent la grille (`datagrid/`, `product/grid/`)
2. Réécrire chaque sous-composant en React fonctionnel (hooks + RTK + react-query)
3. Supprimer le BaseForm correspondant une fois le React validé en prod
4. Répéter jusqu'à supprimer le montage Backbone de la grille

### C2 — Formulaire produit (Product Edit Form)

**Pourquoi en second ?** Le formulaire est le composant le plus complexe (onglets, attributs, associations). Beaucoup de `BaseForm` extensibles.

Même stratégie Strangler Fig : remplacer chaque zone (header, panel, fieldset) par un composant React, tester en parallèle avec Playwright.

### C3 — Nettoyage final

Une fois grille + formulaire migrés, les ~168 fichiers Backbone restants sont des utilitaires (routing, events). Les supprimer et remplacer par :
- Backbone Router → React Router 6 (déjà en place)
- Backbone Events → Zustand ou Context (selon la portée)

**Critère de sortie :** `grep -r "BaseForm\|Backbone.View" src/` → 0 résultats.

---

## Phase D — Qualité et outillage (en parallèle de C)

Ces tâches n'ont pas de dépendances bloquantes et peuvent avancer en parallèle.

### D1 — .js → .ts (822 fichiers)

**Stratégie :** `allowJs: true` est déjà activé. Migration par module, pas en bloc.
Priorité : les fichiers touchés par Phase C d'abord (co-migration).

**Outil :** `ts-migrate` pour bootstrapper, correction manuelle des `any` résiduels.

### D2 — Storybook 6 → 8

**Prérequis :** Phase A terminée (Storybook 8 attend react-query v5 compatible).  
**Gain :** Story-level testing avec `@storybook/test`, Interaction tests, Visual regression.

### D3 — ESLint : activer les règles désactivées

Après migration TS (D1), activer progressivement :
- `@typescript-eslint/no-explicit-any`
- `react-hooks/exhaustive-deps`
- `import/no-cycle`

---

## Séquençage recommandé

```
2026-05-05  ████ Phase 0 (coverage 54→70%)           ← en cours
2026-05-19  ████ Phase A1 (Redux → RTK)
2026-05-26  ████ Phase A2 (react-query v3→v5)
2026-06-09  ████████ Phase B (AMD→ESM, require.js out)
2026-07-07  ████████████████ Phase C1 (Product Grid)
2026-08-18  ████████████████████████ Phase C2 (Product Form)
2026-10-06  ████████ Phase C3 (nettoyage Backbone)
            Phase D en parallèle tout au long
```

---

## Risques principaux

| Risque | Probabilité | Mitigation |
|---|---|---|
| Régression Behat sur grille/formulaire | Haute | Playwright spec par composant migré |
| Dépendances circulaires AMD bloquantes | Moyenne | Madge audit avant Phase B |
| react-query v5 casse des `onSuccess` silencieux | Moyenne | Grep exhaustif + tests ciblés |
| Performance dégradée (bundle split) | Faible | Webpack Bundle Analyzer avant/après |

---

## Métriques de succès

- **Coverage Jest :** 70 % (Phase 0) → 80 % (Phase C)
- **Fichiers AMD :** 488 → 0
- **Fichiers Backbone :** 208 → 0
- **Fichiers `.js` non typés :** 822 → 0
- **Temps de build RSPack :** < 30 s (à mesurer)
- **Core Web Vitals grille produit :** LCP < 2.5 s, TBT < 300 ms

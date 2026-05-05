# Phase A — Modernisation des librairies

## A1 — Redux 4 → Redux Toolkit

### Périmètre

| Fichier | Type |
|---|---|
| `DataQualityInsights/front/src/infrastructure/store/productEditFormStore.ts` | Store |
| `DataQualityInsights/front/src/infrastructure/reducer/ProductEditForm/catalogContextReducer.ts` | Reducer |
| `DataQualityInsights/front/src/infrastructure/reducer/ProductEditForm/pageContextReducer.ts` | Reducer |
| `DataQualityInsights/front/src/infrastructure/reducer/ProductEditForm/productEvaluationReducer.ts` | Reducer |
| `DataQualityInsights/front/src/infrastructure/reducer/ProductEditForm/productFamilyInformationReducer.ts` | Reducer |
| `DataQualityInsights/front/src/infrastructure/reducer/ProductEditForm/productReducer.ts` | Reducer |
| `DataQualityInsights/front/src/infrastructure/reducer/index.ts` | Barrel |
| 5 fichiers de tests unitaires | Tests |

### Ce qui change

| Aspect | Avant | Après |
|---|---|---|
| Dépendance | `redux` + `redux-devtools-extension` | `@reduxjs/toolkit` |
| Création store | `createStore` + `combineReducers` + `composeWithDevTools` | `configureStore({ reducer: {...} })` |
| Création reducer | `switch/case` + types + action creators manuels | `createSlice({ name, initialState, reducers })` |
| Action type string | `'CHANGE_CATALOG_CONTEXT_LOCALE'` | `'catalogContext/changeCatalogContextLocale'` |
| Payload single-arg | `payload: { locale }` | `payload: locale` |
| Payload multi-arg | `prepare` callback ou objet | `prepare` callback |
| State mutation | spread nécessaire | Immer (mutation directe OU return) |

### Breaking changes côté tests

1. Supprimer les blocs `'action type constants'` (ils testent des chaînes supprimées)
2. Mettre à jour les assertions `toEqual({ type: '...', payload: ... })` avec nouvelles chaînes et formes

### Callers (production) — impact

**Aucun** : les signatures des action creators restent identiques (`changeCatalogContextLocale(locale)`, etc.).
Les Listeners dispatchent les mêmes appels — seule la valeur interne de `action.type` change, invisible depuis l'extérieur.

---

## A2 — react-query v3 → v5

### Périmètre (source, hors tests)

- `Category/front` : 24 fichiers (hooks + composants)
- `Connectivity/Connection/front` : 5 fichiers
- `UserManagement` : 2 fichiers
- `UIBundle` : 8+ fichiers (`StorageConfigurator`, `QuantifiedAssociations`, etc.)

### Breaking changes v3→v5

| API v3 | API v5 | Fichiers concernés |
|---|---|---|
| `cacheTime` | `gcTime` | grep: `cacheTime` |
| `useQuery(key, fn, opts)` | `useQuery({ queryKey, queryFn, ...opts })` | tous les `useQuery` |
| `onSuccess`/`onError` dans options | Supprimés → `useEffect` | grep: `onSuccess\|onError` |
| `invalidateQueries(key)` | `invalidateQueries({ queryKey: [key] })` | grep: `invalidateQueries` |
| `import { useQuery } from 'react-query'` | `import { useQuery } from '@tanstack/react-query'` | tous |
| `QueryClientProvider` | Identique (nom de paquet change) | wrappers de test |

### Stratégie

1. Installer `@tanstack/react-query` (v5), ne pas supprimer `react-query` immédiatement
2. Migrer fichier par fichier, tester localement avec `yarn unit`
3. Supprimer `react-query` une fois tous les imports migrés
4. Mettre à jour les wrappers de test (`test-utils.tsx` de chaque workspace)

### Ordre recommandé

1. Connectivity Connection (5 fichiers, plus simples, déjà couverts par tests)
2. UserManagement (2 fichiers)
3. Category (24 fichiers, le plus gros)
4. UIBundle (fichiers Backbone-adjacent, attention aux imports AMD)

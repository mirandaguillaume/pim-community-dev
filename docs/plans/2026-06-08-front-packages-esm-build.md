# Front-packages dual CJS+ESM build (unlock tree-shaking)

Status: **planned** — prerequisite `sideEffects` declarations already on branch
`feat/front-treeshake-cleanup`.

## Why

The app imports the two big front-packages almost entirely through their
**barrel**:

| package | barrel importers | deep-path importers |
|---|---|---|
| `akeneo-design-system` (247 components) | **407 files** | 6 |
| `@akeneo-pim-community/shared` | **426 files** | 43 |

The packages ship **CommonJS** (`lib/`, `tsc --module commonjs`). A CJS barrel
is **not statically tree-shakeable**, so the prod bundle includes the whole of
both packages regardless of how little each file uses. Declaring `sideEffects`
(done) is necessary but **inert** until the consumed code is ESM — measured
gain with sideEffects alone over the CJS lib: **0** (main.min.js 1.76 MiB
unchanged).

## The change

Dual build per package: keep `lib/` (CJS) for `require()` consumers, add `es/`
(ESM) for bundlers.

1. **tsc ESM output**: second build `tsc --module es2020 --moduleResolution bundler
   --outDir es` (new `tsconfig.esm.json` extending the base, overriding module/outDir).
   Keep the existing CJS `lib:build` untouched.
2. **package.json**:
   - keep `"main": "lib/index.js"` (CJS),
   - add `"module": "es/index.js"` (ESM — rspack `target: web` resolves
     `["browser","module","main"]`, so the app picks ESM automatically; **no
     rspack.config change needed**),
   - `sideEffects` already declared,
   - deep-path consumers keep using `/lib/` (CJS) — only 6+43 files, tree-shaking
     irrelevant for them (a deep import already pulls one module).
3. **CI `packages:build`**: run both `lib:build` and the new `esm:build`; include
   `es/` in the `front-packages-lib` artifact + the front-build cache key inputs.

## The real risk — dual-package hazard (React context)

`shared` exposes React contexts/singletons (e.g. `DependenciesProvider`). If
part of the graph resolves the ESM copy and part the CJS copy, there are **two
module instances** → `useContext` returns the wrong/empty value → silent runtime
breakage (the exact swallowed-error class from B7).

Mitigations / checks:
- jest keeps resolving **`main` (CJS)** by default → unit tests untouched (verify
  jest does not set `resolve.mainFields`/`moduleNameMapper` to `module`).
- the rspack app resolves **`module` (ESM)** consistently for ALL importers
  (barrel + deep) → one instance in the app bundle. Confirm rspack does not also
  pull `lib/` for the 6+43 deep `/lib/` paths in the SAME graph (they would be a
  2nd instance). If they do → migrate those deep imports to the barrel, or add an
  `exports` map redirecting `./lib/*` → `./es/*` for the bundler condition.
- `react`, `react-dom`, `styled-components` must stay external/peers in `es/`
  (already peerDependencies) — never bundled.

## Verification (the nets)

1. prod build with `ANALYZE=true` → compare `main.min.js` bytes vs the 1.76 MiB
   baseline AND inspect the bundle-analyzer report for dropped DS components.
2. full unit suite (jest, CJS path) green.
3. behat + playwright E2E green — the app smoke that exercises `DependenciesProvider`
   context (login → grids → PEF) = proof there is no dual-instance.
4. drive the live local PIM, check console for context/`undefined` errors (the
   swallowed-error net from B7).

## Out of scope / follow-ups

- Migrating the 6+43 deep `/lib/` imports to the barrel (only if they cause a 2nd
  instance).
- An `exports` map (cleaner than `main`+`module`, but stricter — defer unless the
  dual-package hazard forces it).

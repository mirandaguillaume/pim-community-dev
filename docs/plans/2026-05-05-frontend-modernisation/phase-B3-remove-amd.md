# Phase B3 — Remove `amd: {}` (finish the AMD → CJS/ESM migration)

Status: **planned / blocked on the in-flight wave PRs merging.** Do NOT execute on
autopilot — removing `amd: {}` changes bundler behaviour for the WHOLE bundle.

## Readiness (verified on master, this session)

- **App `define()` modules: 88 remaining, ALL covered by the 3 in-flight PRs**
  (#228 wave9, #243 wave12, #244 wave13). After they merge → **0 app `define()`**.
  (Earlier waves: fetcher, form/common, controllers, product, job, family,
  attribute, misc, oro-datagrid, leftovers, __moduleConfig — all merged/in-flight.)
- **0 dynamic AMD requires** (`require([...], cb)`) in app JS.
- **0 `requirejs` / `require.config` / `require.toUrl` / `require.defined`** usages.
- So once `define()` = 0, nothing in app code needs the AMD runtime.

## Blockers (NOT covered by the waves)

1. **Akeneo bootstrap AMD shims** (in `frontend/webpack/`, outside the wave scope):
   - `require-polyfill.js` — `define([...])`; the runtime `require(modules, cb)`
     polyfill that hijacks `require()` calls **inside Twig templates** (resolves via
     `requireContext`). Build/runtime-critical.
   - `require-context.js` — `define([...])`; the module-registry resolver.
   These must be migrated to CJS (or rewritten) before `amd: {}` can go.
2. **Vendor UMD libs** (`src/**/Resources/public/lib/*`: bootstrap-modal, jquery-ui,
   summernote, backbone-pageable, jquery.multiselect): NOT a blocker — UMD has a
   CommonJS/global fallback, so they keep working once `define.amd` is absent. Do NOT
   touch them.

## Steps (after the 3 PRs merge)

1. Verify `git grep -lE '^\s*define\(' -- 'src/**/Resources/public/js/**/*.js'` = **0**.
2. Migrate `frontend/webpack/require-polyfill.js` + `require-context.js` to CJS (hardened
   codemod or manual — they are small; verify the Twig `require()` bridge + module
   registry still resolve). This is the highest-risk step — it's the runtime require path.
3. Remove `amd: {}` from **rspack.config.js:33** AND the parallel entry in
   **webpack.config.js**.
4. Build + full E2E verification: bundle builds non-empty; app loads; grids render;
   PEF + mass-edit; Twig inline `require()` calls resolve. The E2E suite is the net
   (no unit tests for legacy modules — see the test-coverage finding).
5. (Optional, separate) Mechanical flip `require`/`module.exports` → `import`/
   `export default` for native ESM + better tree-shaking. Not required to remove amd:{}.
6. **Final cleanup — remove migration tooling** (LAST, only once steps 1–5 are done):
   delete `frontend/codemods/amd-to-cjs.js` AND `frontend/codemods/cjs-to-esm.js`, and
   remove the now-unused `jscodeshift` devDependency from `package.json`. Verified safe to
   remove together: the codemods are pure manual tooling (referenced nowhere in CI / castor
   / scripts) and `jscodeshift` is used by nothing else. The migration record stays in git
   history. Do this LAST — `amd-to-cjs.js` is still needed for step 2 (the bootstrap shims
   are still AMD) and `cjs-to-esm.js` for step 5 (the ESM flip); deleting earlier breaks B3.

## Why this is worth it

`amd: {}` forces RSPack to follow the AMD dependency graph and disables some ESM
optimisations. Removing it lets webpack/rspack treat modules as CJS/ESM and apply
tree-shaking / scope-hoisting → smaller, faster bundle.

## Safety net for this migration (no unit tests for legacy modules)

The proven per-wave protocol this session (0 regressions across 47 files): hardened
codemod (raw destructure + dropped-binding guard-rail, PR #242) → exhaustive `eslint
no-undef` (with ProvidePlugin globals `_`/`Backbone`/`$`/`jQuery` + `__moduleConfig`) →
per-file adversarial AMD-vs-CJS review (one agent per file). Apply the same to the 2
bootstrap shims. CI `no-undef` on migrated files is the recommended permanent guard.

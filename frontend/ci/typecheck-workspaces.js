#!/usr/bin/env node
/**
 * Type-checks the front-end workspaces that have no `tsc` script of their own.
 *
 * Why this exists: there is no repo-wide type-check. The root tsconfig.json includes only
 * `public/bundles` and `frontend/types.d.ts` — build output, not sources — and half the
 * workspaces never run tsc, so rspack strips their types without checking them.
 *
 * Why it generates its config instead of committing one: the module aliases come from
 * `getModulePaths()`, the same helper rspack.config.js uses, and there are 1171 of them. A
 * committed tsconfig would go stale the moment a module is added or moved; reading the live
 * table means this check resolves modules exactly the way the bundler does. Without it, 92% of
 * what tsc reports here is `TS2307: Cannot find module` — noise that hides the real errors.
 *
 * This REPORTS, it does not gate, and that is deliberate. Scoping a type-check by workspace
 * does not work: tsc follows the import graph, so checking the seven workspaces that measure
 * clean still pulls in `legacy-bridge`, which has errors. A gate scoped this way would be red
 * on every run — the same miscalibrated-guard shape this repository already carries too much
 * of. It becomes a gate once the production-code errors are fixed, in one step, with no
 * allowlist and no baseline.
 *
 * Measured 2026-10-21, across the eleven workspaces and 1000+ files:
 *
 *   type errors            39
 *   of them in production    8   (6 in legacy-bridge, 2 in DataQualityInsights/front)
 *   module resolution        0
 *   by code                 TS2339 x28, TS2322 x5, TS2345 x2, TS2741/TS2717/TS2551/TS2353 x1
 *
 * The 31 non-production ones sit in *.unit.tsx and tests/front/. Connectivity/Connection/front
 * is the largest workspace here at 641 files and reports zero.
 */
const fs = require('fs');
const path = require('path');
const {execFileSync} = require('child_process');

const ROOT = path.resolve(__dirname, '../..');
const {getModulePaths} = require(path.join(ROOT, 'frontend/webpack/requirejs-utils'));

// The eleven workspaces with no `tsc` script of their own — the whole blind spot.
const WORKSPACES = [
  'src/Akeneo/Connectivity/Connection/front',
  'src/Akeneo/Connectivity/Connection/workspaces/permission-form',
  'src/Akeneo/Pim/Enrichment/Bundle/Resources/workspaces/enrichment',
  'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/activity',
  'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/product',
  'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/system',
  'src/Akeneo/UserManagement/Bundle/Resources/workspaces/user-ui',
  'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front',
  'src/Akeneo/Pim/Structure/Bundle/Resources/workspaces/settings-ui',
  'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge',
  'src/Akeneo/Pim/Automation/DataQualityInsights/front',
];

const OUT_DIR = path.join(ROOT, 'var/typecheck-workspaces');

const buildPaths = () => {
  const {aliases} = getModulePaths(ROOT, ROOT);
  const paths = {};

  for (const [alias, target] of Object.entries(aliases)) {
    const relative = path.relative(ROOT, target);
    if (relative.startsWith('..')) {
      continue;
    }
    paths[alias] = [relative.replace(/\.(js|jsx|ts|tsx)$/, '')];
  }

  // Workspace-local aliases live in the workspace's own tsconfig, which this generated config
  // does not extend. `@src/*` alone accounts for 472 of the 476 unresolved imports measured
  // before it was added.
  paths['@src/*'] = ['src/Akeneo/Connectivity/Connection/front/src/*'];
  paths['akeneopimstructure/*'] = ['src/Akeneo/Pim/Structure/Bundle/Resources/public/*'];

  return paths;
};

const writeConfig = () => {
  fs.mkdirSync(OUT_DIR, {recursive: true});

  const include = WORKSPACES.flatMap(workspace => [
    path.join(ROOT, workspace, '**/*.ts'),
    path.join(ROOT, workspace, '**/*.tsx'),
  ]);

  const config = {
    compilerOptions: {
      baseUrl: ROOT,
      paths: buildPaths(),
      target: 'es2019',
      module: 'commonjs',
      moduleResolution: 'node',
      jsx: 'react',
      lib: ['es2019', 'dom'],
      allowJs: false,
      esModuleInterop: true,
      allowSyntheticDefaultImports: true,
      resolveJsonModule: true,
      skipLibCheck: true,
      noEmit: true,
      // Deliberately not `strict`: the point is to catch what the bundler silently drops, not
      // to open a 1000-file strictness migration. Raising this is a separate, measurable step.
      strict: false,
      noImplicitAny: false,
    },
    exclude: [path.join(ROOT, '**/node_modules/**')],
    include,
  };

  const file = path.join(OUT_DIR, 'tsconfig.json');
  fs.writeFileSync(file, JSON.stringify(config, null, 2));

  return file;
};

const RESOLUTION_CODES = new Set(['TS2307', 'TS7016', 'TS2792']);

const main = () => {
  const configFile = writeConfig();
  console.log(`Type-checking the ${WORKSPACES.length} workspaces that have no tsc script.`);

  let output = '';
  try {
    execFileSync(path.join(ROOT, 'node_modules/.bin/tsc'), ['-p', configFile, '--noEmit'], {
      encoding: 'utf8',
    });
  } catch (error) {
    output = `${error.stdout || ''}${error.stderr || ''}`;
  }

  const errors = output.split('\n').filter(line => /error TS\d+/.test(line));
  const byCode = {};
  let resolution = 0;
  let production = 0;

  for (const line of errors) {
    const code = line.match(/error (TS\d+)/)[1];
    byCode[code] = (byCode[code] || 0) + 1;
    if (RESOLUTION_CODES.has(code)) {
      resolution++;
    }
    if (!/\.unit\.|\/tests?\/|\.test\./.test(line.split('(')[0])) {
      production++;
    }
  }

  console.log(`\n  type errors            ${errors.length - resolution}`);
  console.log(`  of them in production  ${production}`);
  console.log(`  module resolution      ${resolution}   <- probe-config gaps, not code defects`);
  console.log(`\n  by code: ${JSON.stringify(byCode)}`);
  console.log('\nNothing else in CI reports these.');
};

main();

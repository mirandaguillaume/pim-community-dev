#!/usr/bin/env node
// Checks that the built package loads outside the bundlers that used to hide its defects.
//
// webpack, rspack and Jest resolved export names at run time and intercepted `.svg` requires, so a
// lib/ that only worked inside them looked fine to every consumer of this repository. Two checks:
//
// 1. CommonJS, plain Node: every module of lib/ loads and every `.svg` it requires exists. Node has
//    no loader for `.svg`, so one is registered that only records the file -- resolution, which is
//    what used to fail, still happens first and throws on a missing file.
// 2. ESM, statically: every name the CommonJS build exports is imported from lib/esm by esbuild,
//    which, like rollup and so Vite, resolves ESM named exports at build time. A name missing or
//    made ambiguous by two `export *` fails the bundle, whereas the CommonJS build would still
//    serve it at run time.
const path = require('path');
const fs = require('fs');
const os = require('os');

const root = path.resolve(__dirname, '..');
const cjsEntry = path.join(root, 'lib', 'index.js');
const esmEntry = path.join(root, 'lib', 'esm', 'index.js');

const svgRequired = new Set();
require.extensions['.svg'] = (module, filename) => {
  svgRequired.add(filename);
  module.exports = filename;
};

const cjsExports = Object.keys(require(cjsEntry)).filter(name => name !== '__esModule');
if (0 === cjsExports.length) throw new Error(`${cjsEntry} exports nothing`);
if (0 === svgRequired.size) throw new Error('No .svg was required: the illustrations were not loaded');
// Inside the repository, a path escaping lib/ would still find src/static and pass; a consumer
// that only gets lib/ -- the example project installs `file:../lib` -- would not have it.
const outside = [...svgRequired].filter(file => !file.startsWith(path.join(root, 'lib') + path.sep));
if (0 !== outside.length) throw new Error(`Assets required from outside lib/:\n${outside.join('\n')}`);
console.log(`CommonJS: ${cjsExports.length} exports, ${svgRequired.size} assets resolved inside lib/`);

const outdir = fs.mkdtempSync(path.join(os.tmpdir(), 'dsm-lib-check-'));
try {
  require('esbuild').buildSync({
    stdin: {
      contents: `import {${cjsExports.join(', ')}} from ${JSON.stringify(esmEntry)};\nconsole.log(${cjsExports.length});`,
      resolveDir: root,
      loader: 'js',
    },
    bundle: true,
    format: 'esm',
    // Only lib/esm is under test: react, styled-components and the other dependencies stay out.
    packages: 'external',
    loader: {'.svg': 'file'},
    outdir,
    logLevel: 'error',
  });
} finally {
  fs.rmSync(outdir, {recursive: true, force: true});
}
console.log(`ESM: all ${cjsExports.length} names import statically from lib/esm`);

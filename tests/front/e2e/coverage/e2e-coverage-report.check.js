// Config guard — runs via `node` WITHOUT monocart installed (the converter
// lazy-requires monocart inside main(), so requiring the module here is safe).
const assert = require('assert');
const {buildOptions, OUT_DIR} = require('./e2e-coverage-report');

const o = buildOptions();
assert.deepStrictEqual(o.reports, ['lcovonly'], 'must emit lcovonly');
assert.ok(String(OUT_DIR).endsWith('coverage-e2e'), 'outputs to coverage-e2e');
assert.strictEqual(o.outputDir, OUT_DIR, 'outputDir is the coverage-e2e dir');
assert.strictEqual(o.sourceFilter['**/src/**'], true, 'keeps src sources');
assert.strictEqual(o.sourceFilter['**/public/bundles/**'], true, 'keeps legacy bundle sources');
assert.strictEqual(o.sourceFilter['**/node_modules/**'], false, 'drops node_modules sources');
assert.strictEqual(o.entryFilter['**/node_modules/**'], false, 'drops node_modules entries');
console.log('e2e-coverage-report config check passed');

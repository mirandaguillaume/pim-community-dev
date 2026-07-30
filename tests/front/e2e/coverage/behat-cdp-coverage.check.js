/**
 * Node-runnable checks for the pure parts of behat-cdp-coverage (no browser needed).
 * Run: node tests/front/e2e/coverage/behat-cdp-coverage.check.js
 */
const assert = require('assert');
const {sanitise, toV8Entries} = require('./behat-cdp-coverage');

// A scenario id becomes a safe filename.
assert.strictEqual(sanitise('tests/legacy/features/pim/foo.feature:23'), 'tests-legacy-features-pim-foo-feature-23');

// Profiler.takePreciseCoverage returns {result: [{scriptId, url, functions}]}. monocart wants that
// array with `source` attached, and entries without a usable url dropped.
const cdp = {
  result: [
    {scriptId: '1', url: 'http://httpd/dist/main.min.js', functions: [{functionName: 'f', ranges: []}]},
    {scriptId: '2', url: '', functions: []},
    {scriptId: '3', url: 'chrome-extension://x/y.js', functions: []},
  ],
};
const sources = {1: 'console.log(1)'};
const entries = toV8Entries(cdp, sources);

assert.strictEqual(entries.length, 1, 'only the real http script survives');
assert.strictEqual(entries[0].url, 'http://httpd/dist/main.min.js');
assert.strictEqual(entries[0].source, 'console.log(1)');
assert.ok(Array.isArray(entries[0].functions));

console.log('behat-cdp-coverage checks passed');

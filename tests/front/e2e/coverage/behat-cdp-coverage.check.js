/**
 * Node-runnable checks for the pure parts of behat-cdp-coverage (no browser needed).
 * Run: node tests/front/e2e/coverage/behat-cdp-coverage.check.js
 */
const assert = require('assert');
const {sanitise, toV8Entries, takeCoverage, cdpUrl} = require('./behat-cdp-coverage');

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

// cdpUrl() must read /status, never `GET /session/{id}`. Selenium answers the latter with a 404
// "Cannot call non W3C standard command while in W3C mode", which is what silently emptied the JS
// half of the inventory on its first real run: every startCoverage() threw "se:cdp absent".
const STATUS = {
  value: {
    nodes: [
      {slots: [{session: null}]},
      {
        slots: [
          {session: {sessionId: 'other', capabilities: {'se:cdp': 'ws://sel/other/se/cdp'}}},
          {session: {sessionId: 'wanted', capabilities: {'se:cdp': 'ws://sel/wanted/se/cdp'}}},
        ],
      },
    ],
  },
};

const withFetch = async (handler, run) => {
  const real = global.fetch;
  const seen = [];
  global.fetch = url => {
    seen.push(String(url));
    return Promise.resolve({json: () => Promise.resolve(handler(String(url)))});
  };
  try {
    return {result: await run(), seen};
  } finally {
    global.fetch = real;
  }
};

(async () => {
  const ok = await withFetch(
    () => STATUS,
    () => cdpUrl('http://sel:4444', 'wanted')
  );
  assert.strictEqual(ok.result, 'ws://sel/wanted/se/cdp', 'picks the se:cdp of the REQUESTED session');
  assert.deepStrictEqual(ok.seen, ['http://sel:4444/status'], 'reads /status and nothing else');
  assert.ok(
    !ok.seen.some(u => /\/session\//.test(u)),
    'must never call GET /session/{id} — Selenium 404s it in W3C mode'
  );

  // A session Selenium does not know about must fail loudly, not resolve to undefined.
  const missing = await withFetch(
    () => STATUS,
    () =>
      cdpUrl('http://sel:4444', 'ghost').then(
        () => 'resolved',
        e => e.message
      )
  );
  assert.ok(/not found in Selenium \/status/.test(missing.result), 'unknown session throws');

  // A session present but without the capability is a different failure, and must say so.
  const noCap = await withFetch(
    () => ({value: {nodes: [{slots: [{session: {sessionId: 'wanted', capabilities: {}}}]}]}}),
    () =>
      cdpUrl('http://sel:4444', 'wanted').then(
        () => 'resolved',
        e => e.message
      )
  );
  assert.ok(/exposes no se:cdp/.test(noCap.result), 'missing capability throws its own message');

  // startCoverage() returns null on failure; takeCoverage must tolerate that rather than throwing.
  const n = await takeCoverage(null, 'x:1', '/tmp/nowhere');
  assert.strictEqual(n, 0, 'takeCoverage(null) resolves to 0 rather than throwing');

  console.log('behat-cdp-coverage checks passed');
})();

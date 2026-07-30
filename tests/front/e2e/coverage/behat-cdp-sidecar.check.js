/**
 * Node-runnable checks for the sidecar's pure parts (no browser, no Selenium needed).
 * Run: node tests/front/e2e/coverage/behat-cdp-sidecar.check.js
 */
const assert = require('assert');
const {pickSession, nextState} = require('./behat-cdp-sidecar');

// Selenium's /status nests sessions under nodes[].slots[].session, and carries se:cdp with them.
const status = {
  value: {
    nodes: [
      {slots: [{session: null}]},
      {slots: [{session: {sessionId: 'abc123', capabilities: {'se:cdp': 'ws://sel:4444/session/abc123/se/cdp'}}}]},
    ],
  },
};
assert.deepStrictEqual(pickSession(status), {
  sessionId: 'abc123',
  cdpUrl: 'ws://sel:4444/session/abc123/se/cdp',
});

// No session yet is normal, not an error: the sidecar starts before Behat opens the browser.
assert.strictEqual(pickSession({value: {nodes: [{slots: [{session: null}]}]}}), null);
assert.strictEqual(pickSession({}), null);

// The state machine: a dump is owed for the PREVIOUS test whenever the marker changes.
assert.deepStrictEqual(nextState('', 'a.feature:1'), {dumpFor: null, current: 'a.feature:1'});
assert.deepStrictEqual(nextState('a.feature:1', 'a.feature:1'), {dumpFor: null, current: 'a.feature:1'});
assert.deepStrictEqual(nextState('a.feature:1', 'b.feature:2'), {dumpFor: 'a.feature:1', current: 'b.feature:2'});

// An emptied marker still closes out the test that was running.
assert.deepStrictEqual(nextState('a.feature:1', ''), {dumpFor: 'a.feature:1', current: ''});

console.log('behat-cdp-sidecar checks passed');

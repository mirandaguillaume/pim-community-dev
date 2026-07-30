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

// Fix-round-1 (re-attach-after-drop, finish() reentrancy guard) touched only main()'s internal
// loop state, not this pure surface -- so the full state-machine sequence a real run walks through
// (several scenario changes, a dropped/re-attached session, then shutdown) must still reduce to
// exactly the same dumpFor/current pairs as before the edit.
let prev = '';
const marks = ['a.feature:1', 'a.feature:1', 'b.feature:2', 'c.feature:3', ''];
const dumps = [];
for (const current of marks) {
  const {dumpFor, current: next} = nextState(prev, current);
  if (dumpFor) dumps.push(dumpFor);
  prev = next;
}
assert.deepStrictEqual(dumps, ['a.feature:1', 'b.feature:2', 'c.feature:3']);

console.log('behat-cdp-sidecar checks passed');

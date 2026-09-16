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

// --- Defect C: the PLACEHOLDER slot -------------------------------------------------------------
// While a session is being created Selenium publishes a slot whose sessionId is the literal string
// `reserved` and whose capabilities are a copy of the stereotype -- notably WITHOUT se:cdp. Captured
// live below verbatim; the state lasted ~1.2s, i.e. two or three polls at the default 500ms.
//
// `reserved` is truthy, so a sessionId-truthiness test latches onto it and hands it to
// startCoverage(), which re-reads /status, matches the same placeholder and throws -- producing the
// nightly `startCoverage failed: session reserved exposes no se:cdp capability` verbatim.
const RESERVED_SLOT = {
  session: {
    sessionId: 'reserved',
    capabilities: {browserName: 'chrome', browserVersion: '131.0.6778.204', platformName: 'linux'},
    uri: 'http://192.168.224.2:4444',
  },
};
const REAL_SLOT = {
  session: {
    sessionId: '35072940dfc1de97a1476bc0289de057',
    capabilities: {
      browserName: 'chrome',
      'se:cdp': 'ws://192.168.224.2:4444/session/35072940dfc1de97a1476bc0289de057/se/cdp',
      'se:cdpVersion': '131.0.6778.204',
    },
    uri: 'http://192.168.224.2:4444',
  },
};

// A placeholder alone is "not ready yet", exactly like no session at all -- never a usable session.
assert.strictEqual(
  pickSession({value: {nodes: [{slots: [RESERVED_SLOT]}]}}),
  null,
  'a `reserved` placeholder slot must be skipped, not returned as a session'
);

// And it must not shadow a real session sitting behind it: skip the slot, keep scanning.
assert.deepStrictEqual(
  pickSession({value: {nodes: [{slots: [RESERVED_SLOT, REAL_SLOT]}]}}),
  {
    sessionId: '35072940dfc1de97a1476bc0289de057',
    cdpUrl: 'ws://192.168.224.2:4444/session/35072940dfc1de97a1476bc0289de057/se/cdp',
  },
  'scanning continues past a placeholder to the real session in the next slot'
);

// Same across nodes, not just across slots of one node.
assert.deepStrictEqual(
  pickSession({value: {nodes: [{slots: [RESERVED_SLOT]}, {slots: [REAL_SLOT]}]}}).sessionId,
  '35072940dfc1de97a1476bc0289de057',
  'scanning continues past a placeholder into the next node'
);

// The predicate is se:cdp PRESENCE, not the placeholder's literal spelling: a slot that has a real
// id but no se:cdp is just as unusable, and Selenium is free to rename `reserved` tomorrow.
assert.strictEqual(
  pickSession({value: {nodes: [{slots: [{session: {sessionId: 'real-looking-id', capabilities: {}}}]}]}}),
  null,
  'any session without se:cdp is unusable regardless of what its id looks like'
);

// pickSession's contract to its caller: cdpUrl is never null when a session is returned.
assert.ok(pickSession({value: {nodes: [{slots: [RESERVED_SLOT, REAL_SLOT]}]}}).cdpUrl, 'cdpUrl is always present');

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

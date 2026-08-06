/**
 * Node-runnable checks for the pure parts of behat-cdp-coverage (no browser needed).
 * Run: node tests/front/e2e/coverage/behat-cdp-coverage.check.js
 */
const assert = require('assert');
const fs = require('fs');
const os = require('os');
const path = require('path');
const {
  sanitise,
  toV8Entries,
  takeCoverage,
  cdpUrl,
  probeUpgrade,
  CdpClient,
  startCoverage,
} = require('./behat-cdp-coverage');

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

  await defectATests();
  await defectBTests();

  console.log('behat-cdp-coverage checks passed');
})();

// ================================================================================================
// Defect A -- se:cdp is the BROWSER-level CDP endpoint
// ================================================================================================
//
// Verified live on selenium/standalone-chrome:4.27.0: only Target.* / Browser.* answer on that
// socket. Debugger.enable, Profiler.enable and Profiler.startPreciseCoverage sent unrouted all fail
// with `-32601 'X' wasn't found` -- verbatim the nightly `startCoverage failed: 'Debugger.enable'
// wasn't found`. They start working only after Target.attachToTarget({flatten: true}), with the
// returned sessionId placed at the TOP LEVEL of every outgoing frame (flat mode, as Playwright's
// own CRConnection does). NOT inside params: that is ignored and the command still 404s.
//
// FakeCdpEndpoint below is that contract, so this suite fails on any client that forgets the
// attach, routes the wrong commands, or puts the sessionId in the wrong place.

const PAGE_TARGET = 'A5A8D594954287E9409D12C33E679E91';
const TARGET_SESSION = '23ED77C12C7E0D1CBD956E419205C5C1';

/** Domains that live at page/target level and are unreachable unrouted. */
const PAGE_LEVEL = /^(Debugger|Profiler|Page|Runtime)\./;

function fakeCdpEndpoint({targets} = {}) {
  const targetInfos = targets || [{targetId: PAGE_TARGET, type: 'page', url: 'about:blank', attached: true}];

  return frame => {
    const {id, method, params = {}} = frame;
    const routed = frame.sessionId; // TOP LEVEL only -- params.sessionId is deliberately ignored

    if (PAGE_LEVEL.test(method)) {
      if (routed !== TARGET_SESSION) {
        // The exact wire error the nightly run reported.
        return {id, error: {code: -32601, message: `'${method}' wasn't found`}};
      }
      if (method === 'Profiler.takePreciseCoverage') {
        return {
          id,
          result: {
            result: [
              {
                scriptId: '7',
                url: 'http://httpd/dist/main.min.js',
                functions: [{functionName: 'f', ranges: [{count: 1}]}],
              },
            ],
          },
        };
      }
      if (method === 'Debugger.getScriptSource') return {id, result: {scriptSource: 'console.log(1)'}};
      return {id, result: {}};
    }

    // Browser-level commands must arrive UNROUTED; Chrome answers -32001 for an unknown session.
    if (routed) return {id, error: {code: -32001, message: 'Session with given id not found.'}};
    if (method === 'Target.getTargets') return {id, result: {targetInfos}};
    if (method === 'Target.attachToTarget') {
      if (params.flatten !== true) return {id, error: {code: -32602, message: 'flatten required'}};
      return {id, result: {sessionId: TARGET_SESSION}};
    }
    return {id, error: {code: -32601, message: `'${method}' wasn't found`}};
  };
}

/** Installs a global WebSocket driven by `handler`, runs `run`, always restores the global. */
async function withFakeSocket(handler, run) {
  const real = global.WebSocket;
  const sockets = [];

  global.WebSocket = class {
    constructor(url) {
      this.url = url;
      this.readyState = 0;
      this.sent = [];
      sockets.push(this);
      // Asynchronous, because connect() assigns onopen only after the constructor returns.
      setTimeout(() => {
        this.readyState = 1;
        this.onopen && this.onopen();
      }, 0);
    }
    send(raw) {
      const frame = JSON.parse(raw);
      this.sent.push(frame);
      const reply = handler(frame);
      if (reply !== undefined) this.emit(reply);
    }
    emit(msg) {
      setTimeout(() => this.onmessage && this.onmessage({data: JSON.stringify(msg)}), 0);
    }
    close() {
      this.readyState = 3;
      this.onclose && this.onclose();
    }
  };

  try {
    return await run(sockets);
  } finally {
    global.WebSocket = real;
  }
}

/** /status shaped as Selenium really returns it, for the one session under test. */
const statusFor = sessionId => ({
  value: {
    nodes: [
      {
        slots: [
          {
            session: {
              sessionId,
              capabilities: {'se:cdp': `ws://172.23.0.3:4444/session/${sessionId}/se/cdp`},
            },
          },
        ],
      },
    ],
  },
});

async function withStatusFetch(sessionId, run) {
  const real = global.fetch;
  global.fetch = () => Promise.resolve({json: () => Promise.resolve(statusFor(sessionId))});
  try {
    return await run();
  } finally {
    global.fetch = real;
  }
}

async function defectATests() {
  // --- the headline: startCoverage must SUCCEED against a faithful browser-level endpoint --------
  // On the pre-fix client this returns null, because Debugger.enable goes out unrouted and the
  // endpoint answers `'Debugger.enable' wasn't found` -- which is precisely CI's `JS dumps: 0`.
  await withFakeSocket(fakeCdpEndpoint(), async sockets => {
    const client = await withStatusFetch('sess-1', () => startCoverage('http://sel:4444', 'sess-1'));

    assert.ok(client, 'startCoverage must attach to the page target, not give up on -32601');
    assert.strictEqual(client.sessionId, TARGET_SESSION, 'the flat-mode target session is remembered');
    assert.strictEqual(client.targetId, PAGE_TARGET, 'and the target it belongs to');

    const sent = sockets[0].sent;
    const byMethod = m => sent.find(f => f.method === m);

    // Browser-level discovery/attach must be UNROUTED.
    assert.ok(byMethod('Target.getTargets'), 'discovers targets before enabling any domain');
    assert.strictEqual(byMethod('Target.getTargets').sessionId, undefined, 'Target.getTargets must not be routed');
    const attach = byMethod('Target.attachToTarget');
    assert.ok(attach, 'attaches to the page target');
    assert.strictEqual(attach.sessionId, undefined, 'Target.attachToTarget must not be routed');
    assert.strictEqual(attach.params.flatten, true, 'flat mode is what makes top-level routing work');
    assert.strictEqual(attach.params.targetId, PAGE_TARGET);

    // ...and the attach must come BEFORE the first page-level command, or that command 404s.
    assert.ok(
      sent.findIndex(f => f.method === 'Target.attachToTarget') < sent.findIndex(f => PAGE_LEVEL.test(f.method)),
      'attach must precede every Debugger/Profiler command'
    );

    // Page-level commands must carry the session at the TOP LEVEL of the frame.
    for (const method of ['Debugger.enable', 'Profiler.enable', 'Profiler.startPreciseCoverage']) {
      const frame = byMethod(method);
      assert.ok(frame, `${method} is sent`);
      assert.strictEqual(frame.sessionId, TARGET_SESSION, `${method} must be routed through the target session`);
      assert.strictEqual(
        frame.params.sessionId,
        undefined,
        `${method} must carry sessionId at the top level, not inside params (params routing is ignored)`
      );
    }

    // And the whole point: a dump actually lands on disk through the routed session.
    const outDir = fs.mkdtempSync(path.join(os.tmpdir(), 'cdp-check-'));
    const count = await takeCoverage(client, 'foo.feature:12', outDir);
    assert.strictEqual(count, 1, 'takeCoverage collects through the routed session');
    const dump = JSON.parse(fs.readFileSync(path.join(outDir, 'foo-feature-12.json'), 'utf8'));
    assert.strictEqual(dump[0].url, 'http://httpd/dist/main.min.js');
    assert.strictEqual(dump[0].source, 'console.log(1)', 'Debugger.getScriptSource is routed too');
    assert.strictEqual(client.stale, false, 'a successful dump leaves the client usable');
    fs.rmSync(outDir, {recursive: true, force: true});
  });

  // --- target choice ----------------------------------------------------------------------------
  // The sidecar can win the race against Behat's first navigation and find the tab still on the
  // startup `data:,` page while a real one exists, so a page already showing an http document wins.
  await withFakeSocket(
    fakeCdpEndpoint({
      targets: [
        {targetId: 'BLANK', type: 'page', url: 'data:,'},
        {targetId: 'SERVICE', type: 'service_worker', url: 'http://httpd/sw.js'},
        {targetId: PAGE_TARGET, type: 'page', url: 'http://httpd/#/products'},
      ],
    }),
    async () => {
      const client = await withStatusFetch('sess-2', () => startCoverage('http://sel:4444', 'sess-2'));
      assert.strictEqual(client.targetId, PAGE_TARGET, 'prefers the page already on an http document');
    }
  );

  // A browser with nothing but the startup page still attaches -- to that page.
  await withFakeSocket(fakeCdpEndpoint({targets: [{targetId: 'BLANK', type: 'page', url: 'data:,'}]}), async () => {
    const client = await withStatusFetch('sess-3', () => startCoverage('http://sel:4444', 'sess-3'));
    assert.strictEqual(client.targetId, 'BLANK', 'falls back to the only page target');
  });

  // Never a non-page target: attaching to a service worker would enable the wrong Profiler.
  await withFakeSocket(
    fakeCdpEndpoint({targets: [{targetId: 'SERVICE', type: 'service_worker', url: 'http://httpd/sw.js'}]}),
    async () => {
      const client = await withStatusFetch('sess-4', () => startCoverage('http://sel:4444', 'sess-4'));
      assert.strictEqual(client, null, 'no page target means no coverage, reported as a clean null');
    }
  );

  // --- staleness: when must the session be re-established? --------------------------------------
  // Not per scenario. Verified live, a Backbone full page load is a same-tab cross-document
  // navigation and the page target survives it byte-identical, so the attachment is kept and only
  // dropped when the session genuinely dies. These are the three ways that death is observable.

  // 1. The unsolicited detach event for OUR session.
  await withFakeSocket(fakeCdpEndpoint(), async sockets => {
    const client = await withStatusFetch('sess-5', () => startCoverage('http://sel:4444', 'sess-5'));
    assert.strictEqual(client.stale, false);

    // Another session's detach is none of our business.
    sockets[0].emit({method: 'Target.detachedFromTarget', params: {sessionId: 'SOMEONE-ELSE'}});
    await new Promise(r => setTimeout(r, 5));
    assert.strictEqual(client.stale, false, 'another session detaching must not invalidate ours');

    sockets[0].emit({method: 'Target.detachedFromTarget', params: {sessionId: TARGET_SESSION}});
    await new Promise(r => setTimeout(r, 5));
    assert.strictEqual(client.stale, true, 'our target detaching marks the client for re-attach');
  });

  // 2. `-32001 Session with given id not found.` -- what a routed command gets once the target
  //    session is dead (verified live, identical for a bogus and for a detached session).
  await withFakeSocket(fakeCdpEndpoint(), async sockets => {
    const client = await withStatusFetch('sess-6', () => startCoverage('http://sel:4444', 'sess-6'));
    sockets[0].send = function (raw) {
      const {id} = JSON.parse(raw);
      this.emit({id, error: {code: -32001, message: 'Session with given id not found.'}});
    };
    const outDir = fs.mkdtempSync(path.join(os.tmpdir(), 'cdp-check-'));
    assert.strictEqual(await takeCoverage(client, 'foo.feature:12', outDir), 0);
    assert.strictEqual(client.stale, true, 'a dead target session marks the client for re-attach');
    fs.rmSync(outDir, {recursive: true, force: true});
  });

  // 3. Any other dump failure. takeCoverage swallows its own errors by contract, so the sidecar's
  //    try/catch around it can NEVER fire -- the stale flag is the only channel it has. Without it
  //    one dead session silently zeroes JS coverage for the whole remaining shard.
  await withFakeSocket(fakeCdpEndpoint(), async sockets => {
    const client = await withStatusFetch('sess-7', () => startCoverage('http://sel:4444', 'sess-7'));
    sockets[0].send = () => {
      throw new Error('socket is closed');
    };
    assert.strictEqual(await takeCoverage(client, 'foo.feature:12', os.tmpdir()), 0, 'still never throws');
    assert.strictEqual(client.stale, true, 'and always leaves a signal the sidecar can act on');
  });

  // 4. A socket that genuinely closes must settle its in-flight commands at once rather than let
  //    them run out the command timeout, which would stall the sidecar's poll loop.
  await withFakeSocket(fakeCdpEndpoint(), async sockets => {
    const client = new CdpClient('ws://sel:4444/session/x/se/cdp');
    await client.connect();
    sockets[0].send = () => {}; // accept the frame, never answer
    const pending = client.sendBrowser('Target.getTargets').then(
      () => 'resolved',
      e => e.message
    );
    sockets[0].close();
    assert.match(await pending, /closed/, 'a closed socket rejects in-flight commands immediately');
    assert.strictEqual(client.stale, true, 'and marks the client for re-attach');
  });
}

// ================================================================================================
// Defect B -- the upgrade probe must actually reach Selenium
// ================================================================================================

async function defectBTests() {
  const real = global.fetch;

  try {
    // `Connection` and `Upgrade` are forbidden header names in fetch: undici rejects the Request
    // inside its constructor, before any I/O, with UND_ERR_INVALID_ARG. The old probe set them, so
    // it reported `unreachable (UND_ERR_INVALID_ARG)` on EVERY call -- masking the very cause it
    // existed to reveal. This fetch reproduces that validation exactly.
    let sawHeaders = null;
    global.fetch = (url, init) => {
      sawHeaders = (init && init.headers) || {};
      const forbidden = Object.keys(sawHeaders).find(h => /^(connection|upgrade)$/i.test(h));
      if (forbidden) {
        const err = new TypeError('fetch failed');
        err.cause = Object.assign(new Error('invalid connection header'), {code: 'UND_ERR_INVALID_ARG'});
        return Promise.reject(err);
      }
      return Promise.resolve({
        status: 404,
        statusText: 'Not Found',
        text: () =>
          Promise.resolve(
            JSON.stringify({
              value: {
                error: 'invalid session id',
                stacktrace: 'org.openqa.selenium.NoSuchSessionException: Unable to find session with ID: dead',
              },
            })
          ),
      });
    };

    const gone = await probeUpgrade('ws://172.23.0.3:4444/session/dead/se/cdp');
    assert.ok(!/UND_ERR_INVALID_ARG/.test(gone), 'the probe must not trip undici header validation');
    assert.ok(!/unreachable/.test(gone), 'Selenium answered, so the probe must not claim unreachable');
    assert.match(gone, /404/, 'reports the status Selenium returned');
    assert.match(gone, /invalid session id/, 'and the body field that says the session is gone');
    assert.deepStrictEqual(sawHeaders, {}, 'no headers are set on the probe request at all');

    // Both a live and a dead session answer 404 on a plain GET of .../se/cdp (verified live), so
    // the status alone proves nothing -- only value.error separates them. A live session says
    // "unknown command", i.e. the websocket upgrade is the problem, not the session.
    global.fetch = () =>
      Promise.resolve({
        status: 404,
        statusText: 'Not Found',
        text: () =>
          Promise.resolve(
            JSON.stringify({value: {error: 'unknown command', message: 'unknown command: session/abc/se/cdp'}})
          ),
      });
    const alive = await probeUpgrade('ws://172.23.0.3:4444/session/abc/se/cdp');
    assert.match(alive, /unknown command/, 'a live session is distinguishable from a dead one');
    assert.notStrictEqual(alive, gone, 'the two 404s must not produce the same message');

    // A body that is not JSON must not break the probe: the status is still worth reporting.
    global.fetch = () =>
      Promise.resolve({status: 502, statusText: 'Bad Gateway', text: () => Promise.resolve('<html>nope</html>')});
    assert.match(await probeUpgrade('ws://sel:4444/x'), /502/, 'a non-JSON body still yields the status');

    // Transport failures never reached Selenium. This branch was always right; keep it right.
    global.fetch = () => {
      const err = new TypeError('fetch failed');
      err.cause = Object.assign(new Error('connect ECONNREFUSED'), {code: 'ECONNREFUSED'});
      return Promise.reject(err);
    };
    const refused = await probeUpgrade('ws://sel:9999/x');
    assert.match(refused, /unreachable/, 'nothing listening is genuinely unreachable');
    assert.match(refused, /ECONNREFUSED/, 'and says so with the cause code');

    // probeUpgrade is only ever called to enrich an existing error, so it must never throw itself.
    global.fetch = () => {
      throw new Error('synchronous boom');
    };
    assert.match(await probeUpgrade('ws://sel:4444/x'), /unreachable/, 'a throwing fetch is still handled');
  } finally {
    global.fetch = real;
  }
}

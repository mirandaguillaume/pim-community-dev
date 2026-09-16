/**
 * Per-scenario JS coverage for the Behat suite, over Selenium's CDP endpoint.
 *
 * Behat drives Pim\Behat\Extension\WebdriverClassicExtension — the CLASSIC WebDriver protocol,
 * which has no CDP of its own. But Selenium exposes one per session: GET /session/{id} returns
 * capabilities including `se:cdp`, a websocket URL. Verified on this stack
 * (selenium/standalone-chrome:4.27.0, se:cdpVersion 131.0.6778.204).
 *
 * That is why this exists instead of an instrumented bundle: driving Profiler over CDP yields the
 * same raw V8 entries Playwright's page.coverage produces, so the existing monocart pipeline
 * (e2e-coverage-report.js) consumes them unchanged — no second build artifact, no SWC/Babel
 * instrumentation, and no window.__coverage__ flushing across Backbone full page loads.
 *
 * Node 22 has a global WebSocket, so this needs no dependency.
 *
 * Best-effort throughout: any failure warns and resolves, never throws into a scenario.
 */
const fs = require('fs');
const path = require('path');

/** A scenario id such as `path/to/foo.feature:23` becomes a safe filename. */
function sanitise(testId) {
  return String(testId)
    .replace(/[^0-9a-z]+/gi, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Reshape Profiler.takePreciseCoverage output into the array monocart expects, attaching each
 * script's source. Entries without an http(s) url are dropped: extension and internal scripts
 * cannot be mapped back to repository sources and would only add noise.
 */
function toV8Entries(cdpResult, sourcesByScriptId) {
  const result = (cdpResult && cdpResult.result) || [];

  return result
    .filter(e => typeof e.url === 'string' && /^https?:\/\//.test(e.url))
    .map(e => ({
      scriptId: e.scriptId,
      url: e.url,
      functions: e.functions || [],
      source: sourcesByScriptId[e.scriptId] || '',
    }));
}

/**
 * Turn a failed websocket upgrade into an actionable line.
 *
 * `WebSocket` surfaces every upgrade failure as the same opaque "Received network error or non-101
 * status code", so re-requesting the same URL over plain http is what tells the causes apart: a
 * transport error means the address was never reachable from this container, while a response
 * means Selenium answered and the BODY says which way.
 *
 * The status code alone does NOT discriminate -- verified live: a plain GET on `.../se/cdp` is 404
 * both for a live session (`value.error: "unknown command"`, i.e. the session is fine and only the
 * non-upgrade request was rejected) and for a dead one (`value.error: "invalid session id"`,
 * NoSuchSessionException). Only `value.error` separates them, which is why it is read out here.
 *
 * NO Connection/Upgrade headers: both are forbidden header names in fetch, and undici rejects the
 * Request inside its constructor (`UND_ERR_INVALID_ARG: invalid connection header`) before any I/O
 * happens. The previous version set them, so every probe reported "unreachable" no matter what
 * Selenium would have said -- actively masking the cause it was written to reveal.
 *
 * Best-effort like everything else here: it only ever enriches a message that is already an error.
 */
async function probeUpgrade(wsUrl) {
  const httpUrl = String(wsUrl).replace(/^ws:/, 'http:').replace(/^wss:/, 'https:');
  try {
    const res = await fetch(httpUrl);
    let reason = '';
    try {
      const value = (JSON.parse(await res.text()) || {}).value || {};
      reason = value.error || value.message || String(value.stacktrace || '').split('\n')[0] || '';
    } catch {
      /* a non-JSON body leaves the status as all we have; that is still more than nothing */
    }
    return ['http probe ->', res.status, res.statusText || '', reason && `(${reason})`].filter(Boolean).join(' ');
  } catch (e) {
    // Transport failures never reached Selenium at all: ECONNREFUSED (nothing listening),
    // EAI_AGAIN (name does not resolve). This branch was always correct -- only the request was not.
    return `http probe -> unreachable (${(e && (e.cause ? e.cause.code || e.cause.message : e.message)) || 'unknown'})`;
  }
}

/**
 * How long a single CDP command may take before it is given up on.
 *
 * This doubles as the ONLY detector of a dead Selenium session: verified live, deleting the
 * WebDriver session leaves the se:cdp websocket in readyState OPEN indefinitely (polled for 60s;
 * no close event, no error event), so nothing but a timeout can reveal it. That makes the value a
 * direct cost -- the sidecar's poll loop blocks for it once when the browser goes away. 15s is
 * ample: unlike connect(), these commands are answered from V8 internals and do not wait on page
 * load. The 30s this used to be made a recycled session cost 31s of wall clock (measured).
 */
const CDP_COMMAND_TIMEOUT_MS = 15000;

/**
 * Minimal CDP client over the session's se:cdp websocket.
 *
 * se:cdp is the BROWSER-level endpoint. Only `Target.*`, `Browser.*` and friends exist there;
 * `Debugger.*` and `Profiler.*` live at page/target level and are reachable only after
 * `Target.attachToTarget`. Sending Debugger.enable straight down this socket is answered
 * `-32601 'Debugger.enable' wasn't found` -- verbatim the nightly failure this class had to be
 * rewritten for. Hence the two send paths: sendBrowser() for the browser-level commands that must
 * stay unrouted, send() for everything that must carry the attached target's session.
 */
class CdpClient {
  constructor(url) {
    this.url = url;
    this.nextId = 1;
    this.pending = new Map();
    this.ws = null;
    /** Flat-mode CDP session for the attached page target; null until attachToPageTarget(). */
    this.sessionId = null;
    this.targetId = null;
    /** Set once the target session is known to be gone, so the sidecar can re-attach. */
    this.stale = false;
  }

  connect() {
    return new Promise((resolve, reject) => {
      this.ws = new WebSocket(this.url);
      // A stalled handshake fires neither onopen nor onerror, so without this the whole
      // sidecar hangs forever. send() already guards the same way.
      const timer = setTimeout(() => reject(new Error('cdp connect timeout')), 30000);
      this.ws.onopen = () => {
        clearTimeout(timer);
        resolve();
      };
      this.ws.onerror = e => {
        clearTimeout(timer);
        // Name the URL and probe it over plain HTTP before giving up. "non-101" collapses three
        // very different causes -- 404 (session gone), 403 (refused), ECONNREFUSED (unroutable) --
        // and without the distinction the next failure costs another two-hour run to characterise.
        probeUpgrade(this.url).then(detail =>
          reject(new Error(`cdp connect failed: ${e.message || 'unknown'} [url=${this.url}] ${detail}`))
        );
      };
      this.ws.onclose = () => {
        // Defensive, and knowingly NOT the common case: deleting the WebDriver session does NOT
        // close this socket (verified live -- readyState stayed OPEN for 60s with no close and no
        // error event), which is why CDP_COMMAND_TIMEOUT_MS has to carry that detection instead.
        // But when the socket does genuinely go (Selenium restarted, network cut), settling the
        // in-flight commands here saves the loop from waiting out a timeout it cannot win.
        this.stale = true;
        for (const [id, p] of this.pending) {
          this.pending.delete(id);
          p.reject(new Error('cdp socket closed'));
        }
      };
      this.ws.onmessage = ev => {
        let msg;
        try {
          msg = JSON.parse(ev.data);
        } catch {
          return;
        }
        if (msg.id === undefined) {
          this._onEvent(msg);
          return;
        }
        const p = this.pending.get(msg.id);
        if (p) {
          this.pending.delete(msg.id);
          if (msg.error) {
            // `-32001 Session with given id not found.` is exactly what a routed command gets once
            // its target session dies (verified live, identical for a bogus and for a detached
            // session). It is the one error that means "re-attach", not "retry".
            if (/session with given id not found/i.test(msg.error.message || '')) this.stale = true;
            p.reject(new Error(msg.error.message));
          } else {
            p.resolve(msg.result);
          }
        }
      };
    });
  }

  /**
   * Unsolicited events. Target.attachedToTarget arrives alongside our own attach response and is
   * pure noise; Target.detachedFromTarget for OUR session means the page target is gone and every
   * later routed command would fail.
   */
  _onEvent(msg) {
    if (msg.method === 'Target.detachedFromTarget' && (msg.params || {}).sessionId === this.sessionId) {
      this.stale = true;
    }
  }

  /** Browser-level command: deliberately UNROUTED (only Target and Browser domains live there). */
  sendBrowser(method, params = {}) {
    return this._send(method, params, null);
  }

  /** Page-level command: routed through the attached target session once there is one. */
  send(method, params = {}) {
    return this._send(method, params, this.sessionId);
  }

  _send(method, params, sessionId) {
    const id = this.nextId++;
    return new Promise((resolve, reject) => {
      this.pending.set(id, {resolve, reject});
      const frame = {id, method, params};
      // FLAT mode: the routing session goes at the TOP LEVEL of the frame, not inside params.
      // This is what Playwright's own CRConnection does, and what the live probe proved unlocks
      // Debugger.enable / Profiler.enable / Profiler.startPreciseCoverage over se:cdp.
      if (sessionId) frame.sessionId = sessionId;
      this.ws.send(JSON.stringify(frame));
      // unref: a command that already answered leaves this timer armed, and a ref'd one would keep
      // the process alive for its full duration after the work is done -- the sidecar's own exit,
      // and the check suite's, would both hang on it.
      const timer = setTimeout(() => {
        if (this.pending.delete(id)) reject(new Error(`cdp timeout: ${method}`));
      }, CDP_COMMAND_TIMEOUT_MS);
      if (typeof timer.unref === 'function') timer.unref();
    });
  }

  /**
   * Attach to the browsing context's page target in flat mode and remember its session.
   *
   * Prefers a page already showing an http(s) document, because the sidecar can win the race
   * against Behat's first navigation and find the tab still on `data:,` while a real one exists.
   * Falls back to the first page target: SE_NODE_MAX_SESSIONS=1 means one tab in practice.
   */
  async attachToPageTarget() {
    const {targetInfos = []} = (await this.sendBrowser('Target.getTargets')) || {};
    const pages = targetInfos.filter(t => t && t.type === 'page');
    if (!pages.length) throw new Error('no page target to attach to');

    const target = pages.find(t => /^https?:/i.test(t.url || '')) || pages[0];
    const {sessionId} =
      (await this.sendBrowser('Target.attachToTarget', {targetId: target.targetId, flatten: true})) || {};
    if (!sessionId) throw new Error('Target.attachToTarget returned no sessionId');

    this.targetId = target.targetId;
    this.sessionId = sessionId;
    this.stale = false;
    return sessionId;
  }

  close() {
    try {
      this.ws && this.ws.close();
    } catch {
      /* ignore */
    }
  }
}

/**
 * Read the session's se:cdp websocket URL from Selenium's /status.
 *
 * NOT from `GET /session/{id}`: that is not a W3C command, and Selenium answers it with
 * 404 `unknown command: Cannot call non W3C standard command while in W3C mode`. Reading it there
 * is what made every startCoverage() fail with "se:cdp absent from session capabilities" and left
 * the JS half of the inventory empty on the first real run.
 *
 * /status does carry it. Probed against this stack (selenium/standalone-chrome:4.27.0), the slot's
 * session capabilities include se:cdp, se:cdpVersion (131.0.6778.204) and se:bidiEnabled=false --
 * the same value the session-creation response returns, which is where it was originally verified.
 * That was the error: confirming the capability exists somewhere, then reading it somewhere else.
 */
async function cdpUrl(seleniumBase, sessionId) {
  const res = await fetch(`${seleniumBase}/status`);
  const body = await res.json();
  for (const node of ((body || {}).value || {}).nodes || []) {
    for (const slot of node.slots || []) {
      const session = slot.session;
      if (session && session.sessionId === sessionId) {
        const url = (session.capabilities || {})['se:cdp'];
        if (!url) throw new Error(`session ${sessionId} exposes no se:cdp capability`);
        return url;
      }
    }
  }
  throw new Error(`session ${sessionId} not found in Selenium /status`);
}

async function startCoverage(seleniumBase, sessionId) {
  let client = null;
  try {
    client = new CdpClient(await cdpUrl(seleniumBase, sessionId));
    await client.connect();
    // MUST come before any Debugger/Profiler command: se:cdp is the browser-level endpoint, where
    // neither domain exists. Unrouted, all three calls below answer `-32601 'X' wasn't found` --
    // the nightly failure. Attaching in flat mode yields the session that unlocks them.
    await client.attachToPageTarget();
    // Debugger.enable is REQUIRED, not optional: without it Chromium keeps no parsed-script
    // registry, so every later Debugger.getScriptSource fails and every entry ships source:''.
    // monocart would then have no bundle text to map ranges into -- silently defeating the whole
    // raw-V8 route. Playwright's own crCoverage.ts enables Debugger before getScriptSource too.
    await client.send('Debugger.enable');
    await client.send('Profiler.enable');
    // callCount MUST be true for a per-scenario inventory, and this was measured, not reasoned:
    //
    //   callCount:false  take#1 -> app1.js [(top):1, alpha:1, beta:1, never1:0]
    //                    take#2 after calling beta() again -> (no entries at all)
    //   callCount:true   take#1 -> app1.js [(top):1, alpha:2, beta:2, never1:0]
    //                    take#2 after calling beta() again -> app1.js [alpha:1, beta:1]
    //
    // In binary mode each script is reportable exactly ONCE per document, so the second scenario to
    // touch already-loaded code gets an empty dump -- it looks like "this scenario exercised no JS"
    // when it exercised plenty. Counting mode yields a true per-take delta, which is exactly the
    // question this inventory asks: what did THIS scenario execute. The extra cost is per-function
    // counters the collector already discards.
    await client.send('Profiler.startPreciseCoverage', {callCount: true, detailed: true});
    return client;
  } catch (e) {
    // Close the socket we opened: before this, a failure after connect() leaked the websocket, and
    // the sidecar retries every poll -- one dead Selenium meant thousands of orphaned sockets.
    if (client) client.close();
    // Best-effort: a coverage failure must never throw into a Behat scenario.
    console.warn(`[cdp] startCoverage failed: ${e.message}`);
    return null;
  }
}

async function takeCoverage(client, testId, outDir) {
  if (!client) return 0; // startCoverage failed; nothing to collect
  try {
    const cdpResult = await client.send('Profiler.takePreciseCoverage');
    const sources = {};

    for (const entry of cdpResult.result || []) {
      try {
        const {scriptSource} = await client.send('Debugger.getScriptSource', {scriptId: entry.scriptId});
        sources[entry.scriptId] = scriptSource;
      } catch {
        // a script may already be gone after a navigation; its entry is still useful without source
      }
    }

    const entries = toV8Entries(cdpResult, sources);
    if (!entries.length) return 0;

    // Each entry carries its script's FULL source (fetched above), and this writes one such dump
    // PER SCENARIO -- the known size driver for the artifact (see coverage-inventory.yml's
    // commit-inventory job timeout comment). Deliberately not deduplicated here: a per-shard
    // `scriptId+url -> source` sidecar, with dumps referencing it instead of embedding, is the
    // follow-up if the artifact proves unwieldy in practice -- not implemented now.
    fs.mkdirSync(outDir, {recursive: true});
    fs.writeFileSync(path.join(outDir, `${sanitise(testId)}.json`), JSON.stringify(entries));

    return entries.length;
  } catch (e) {
    console.warn(`[cdp] takeCoverage failed for ${testId}: ${e.message}`);
    // This function swallows its own errors by contract, so the sidecar's try/catch around it can
    // never fire and can never drop a dead client. Flag it here instead: `stale` is what the
    // sidecar polls to decide to re-attach, and without it one dead session silently zeroes JS
    // coverage for the whole rest of the shard.
    //
    // But ONLY for CDP-transport failures. Marking stale forces a re-attach AND a fresh
    // startPreciseCoverage, which resets the counters -- so classifying a disk error as a dead
    // session discards real coverage to recover from something that was never a session problem.
    // Measured: an ENOTDIR outDir set stale=true, i.e. a transient disk fault silently threw away
    // the shard's accumulated deltas. fs error codes are the discriminator; a CDP failure has none.
    if (!e || !e.code) client.stale = true;
    return 0;
  }
}

module.exports = {sanitise, toV8Entries, probeUpgrade, CdpClient, cdpUrl, startCoverage, takeCoverage};

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

/** Minimal CDP client over the session's se:cdp websocket. */
class CdpClient {
  constructor(url) {
    this.url = url;
    this.nextId = 1;
    this.pending = new Map();
    this.ws = null;
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
        reject(new Error(`cdp connect failed: ${e.message || 'unknown'}`));
      };
      this.ws.onmessage = ev => {
        let msg;
        try {
          msg = JSON.parse(ev.data);
        } catch {
          return;
        }
        const p = this.pending.get(msg.id);
        if (p) {
          this.pending.delete(msg.id);
          msg.error ? p.reject(new Error(msg.error.message)) : p.resolve(msg.result);
        }
      };
    });
  }

  send(method, params = {}) {
    const id = this.nextId++;
    return new Promise((resolve, reject) => {
      this.pending.set(id, {resolve, reject});
      this.ws.send(JSON.stringify({id, method, params}));
      setTimeout(() => {
        if (this.pending.delete(id)) reject(new Error(`cdp timeout: ${method}`));
      }, 30000);
    });
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
  try {
    const client = new CdpClient(await cdpUrl(seleniumBase, sessionId));
    await client.connect();
    // Debugger.enable is REQUIRED, not optional: without it Chromium keeps no parsed-script
    // registry, so every later Debugger.getScriptSource fails and every entry ships source:''.
    // monocart would then have no bundle text to map ranges into -- silently defeating the whole
    // raw-V8 route. Playwright's own crCoverage.ts enables Debugger before getScriptSource too.
    await client.send('Debugger.enable');
    await client.send('Profiler.enable');
    await client.send('Profiler.startPreciseCoverage', {callCount: false, detailed: true});
    return client;
  } catch (e) {
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
    return 0;
  }
}

module.exports = {sanitise, toV8Entries, CdpClient, cdpUrl, startCoverage, takeCoverage};

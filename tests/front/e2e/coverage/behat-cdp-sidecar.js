/**
 * Bridges the PHP Behat run to the Node CDP helper.
 *
 * Behat runs in the httpd container; this runs in the node service; there is no direct call path
 * between them. The bridge is the marker file CoverageMarkerContext already writes before every
 * scenario — both containers share the ./:/srv/pim bind mount, so watching it needs no PHP change
 * at all.
 *
 * Whenever the marker changes, the scenario that just ENDED is the one whose coverage is owed, so
 * the dump is written for the previous id, not the new one.
 *
 * Selenium's GET /status carries each active session's sessionId together with its se:cdp URL, and
 * SE_NODE_MAX_SESSIONS=1 means there is at most one — so session discovery is a single request with
 * no ambiguity.
 *
 * Best-effort throughout: nothing here may disturb the Behat run. Every failure warns and the loop
 * continues.
 */
const fs = require('fs');
const path = require('path');
const {startCoverage, takeCoverage} = require('./behat-cdp-coverage');

/**
 * Extract the one usable active session from Selenium's /status payload, or null if none yet.
 *
 * A slot is only usable once it publishes se:cdp. While a session is being created Selenium fills
 * the slot with a PLACEHOLDER whose sessionId is the literal string `reserved` and whose
 * capabilities are a copy of the stereotype -- no se:cdp key. Observed live to last ~1.2s, which is
 * two or three polls at 500ms. Accepting it on sessionId truthiness alone is what produced the
 * nightly `session reserved exposes no se:cdp capability`: the id was handed to startCoverage(),
 * which re-read /status, matched the same placeholder slot and threw on the absent capability.
 *
 * The predicate is se:cdp presence rather than `sessionId !== 'reserved'` because se:cdp is what
 * the caller actually needs; matching the placeholder's literal text would silently start failing
 * the day Selenium words it differently. Slots are skipped, not fatal: the real session may be in
 * another slot or node.
 */
function pickSession(status) {
  const nodes = ((status || {}).value || {}).nodes || [];
  for (const node of nodes) {
    for (const slot of node.slots || []) {
      const session = slot.session;
      if (!session || !session.sessionId) continue;
      const cdpUrl = (session.capabilities || {})['se:cdp'];
      if (!cdpUrl) continue;
      return {sessionId: session.sessionId, cdpUrl};
    }
  }
  return null;
}

/**
 * Decide what to do when the marker reads `current` and previously read `prev`.
 * A changed marker means the previous scenario finished and its coverage is owed.
 */
function nextState(prev, current) {
  return {dumpFor: prev && prev !== current ? prev : null, current};
}

function readMarker(markerDir) {
  try {
    return fs.readFileSync(path.join(markerDir, '.current-test'), 'utf8').trim();
  } catch {
    return ''; // absent before the first scenario — normal
  }
}

async function main() {
  const [seleniumBase, markerDir, outDir, pollMs] = process.argv.slice(2);
  if (!seleniumBase || !markerDir || !outDir) {
    console.warn('[cdp-sidecar] usage: node behat-cdp-sidecar.js <selenium-url> <marker-dir> <out-dir> [poll-ms]');
    return;
  }
  const interval = Number(pollMs) || 500;

  let client = null;
  let prev = '';
  let dumped = 0;
  let stopping = false;

  // Stop when Behat signals it is done, so the CI step can wait on this process rather than kill it.
  const stopFile = path.join(markerDir, '.coverage-done');
  process.on('SIGTERM', () => finish());
  process.on('SIGINT', () => finish());

  async function finish() {
    // SIGINT and SIGTERM can both arrive (CI often escalates one to the other), and a signal can
    // land while the loop's own dump is in flight. Without this guard both paths would dump the
    // same id, close the same socket and exit twice.
    if (stopping) return;
    stopping = true;

    if (client && prev) {
      try {
        dumped += (await takeCoverage(client, prev, outDir)) ? 1 : 0;
      } catch (e) {
        console.warn(`[cdp-sidecar] final dump failed: ${e.message}`);
      }
    }
    if (client) client.close();
    console.log(`[cdp-sidecar] stopped after ${dumped} dumps`);
    process.exit(0);
  }

  console.log(`[cdp-sidecar] watching ${markerDir} every ${interval}ms`);

  for (;;) {
    if (fs.existsSync(stopFile)) {
      await finish();
      return;
    }

    // The attachment is deliberately NOT rebuilt per scenario. A Backbone full page load is a
    // same-tab cross-document navigation, and the page target survives it: verified live, the
    // targetId is byte-identical after two full navigations and the flat session attached to it
    // keeps answering Profiler.takePreciseCoverage. Re-attaching every scenario would cost a
    // needless round trip and, worse, the fresh Profiler.startPreciseCoverage would reset the
    // counters and discard whatever ran between the marker flip and the re-attach.
    //
    // It is rebuilt only when the session genuinely dies -- the browser session recycled, the tab
    // closed -- which CDP reports unambiguously as `-32001 Session with given id not found` or
    // announces via Target.detachedFromTarget. Both set `stale`. Note the re-attach necessarily
    // yields a NEW CDP sessionId (verified live: the old one is never revived), which is why this
    // drops the whole client rather than trying to reuse it.
    if (client && client.stale) {
      console.warn('[cdp-sidecar] CDP target session is gone; re-attaching');
      client.close();
      client = null;
    }

    // Attach lazily: the browser session does not exist until Behat opens it.
    if (!client) {
      try {
        const res = await fetch(`${seleniumBase}/status`);
        const session = pickSession(await res.json());
        if (session) {
          client = await startCoverage(seleniumBase, session.sessionId);
          // startCoverage() is best-effort and returns null on failure. Logging "attached"
          // unconditionally, as this did, claimed success on every failed attach and is why the
          // logs read as if coverage were running while `JS dumps produced: 0`.
          if (client) {
            console.log(`[cdp-sidecar] attached to session ${session.sessionId} (cdp target ${client.sessionId})`);
          }
        }
      } catch (e) {
        console.warn(`[cdp-sidecar] attach failed (will retry): ${e.message}`);
      }
    }

    const current = readMarker(markerDir);
    const {dumpFor, current: next} = nextState(prev, current);

    if (client && dumpFor) {
      try {
        if (await takeCoverage(client, dumpFor, outDir)) dumped++;
      } catch (e) {
        console.warn(`[cdp-sidecar] dump for ${dumpFor} failed: ${e.message}`);
        // Drop the client so the next tick re-attaches. Without this, one dead session silently
        // zeroes JS coverage for the entire rest of the shard -- the exact failure this exists to
        // prevent -- because `client` stays truthy and the `if (!client)` attach never runs again.
        client = null;
      }
    }
    prev = next;

    await new Promise(r => setTimeout(r, interval));
  }
}

if (require.main === module) {
  main().catch(e => console.warn(`[cdp-sidecar] fatal (ignored): ${e.message}`));
}

module.exports = {pickSession, nextState, readMarker};

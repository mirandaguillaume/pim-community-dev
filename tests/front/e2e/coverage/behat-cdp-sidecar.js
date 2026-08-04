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

/** Extract the one active session from Selenium's /status payload, or null if none yet. */
function pickSession(status) {
  const nodes = ((status || {}).value || {}).nodes || [];
  for (const node of nodes) {
    for (const slot of node.slots || []) {
      const session = slot.session;
      if (session && session.sessionId) {
        return {
          sessionId: session.sessionId,
          cdpUrl: (session.capabilities || {})['se:cdp'] || null,
        };
      }
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

    // Attach lazily: the browser session does not exist until Behat opens it.
    if (!client) {
      try {
        const res = await fetch(`${seleniumBase}/status`);
        const session = pickSession(await res.json());
        if (session) {
          client = await startCoverage(seleniumBase, session.sessionId);
          console.log(`[cdp-sidecar] attached to session ${session.sessionId}`);
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

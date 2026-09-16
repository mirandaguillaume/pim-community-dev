/**
 * Convert raw Playwright V8 JS-coverage dumps (written per-test by the coverage
 * fixture) into an lcov report via monocart-coverage-reports, which unpacks the
 * original sources from the rspack source maps (devtool:'source-map') and
 * reconstructs the FULL per-file line denominator — unlike the previous raw
 * v8-to-istanbul, which reported a degenerate 100%.
 *
 * Best-effort: any failure logs and the process still exits 0 (never fails the
 * E2E job). Reads coverage-v8 (shard)/*.json → writes coverage-e2e/lcov.info.
 */
const fs = require('fs');
const path = require('path');

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const V8_DIR = path.join(REPO_ROOT, 'coverage-v8');
const OUT_DIR = path.join(REPO_ROOT, 'coverage-e2e');

/** monocart options: lcov output; keep only src/** + public/bundles/** sources. */
function buildOptions() {
  return {
    name: 'E2E Playwright Coverage',
    outputDir: OUT_DIR,
    baseDir: REPO_ROOT,
    logging: 'error',
    reports: ['lcovonly'],
    entryFilter: {'**/node_modules/**': false, '**/*': true},
    sourceFilter: {'**/node_modules/**': false, '**/src/**': true, '**/public/bundles/**': true},
  };
}

function listDumps(dir) {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const name of fs.readdirSync(dir)) {
    const full = path.join(dir, name);
    if (fs.statSync(full).isDirectory()) out.push(...listDumps(full));
    else if (name.endsWith('.json')) out.push(full);
  }
  return out;
}

async function main() {
  const dumps = listDumps(V8_DIR);
  if (!dumps.length) {
    console.warn(`[e2e-coverage] no dumps under ${V8_DIR}; nothing to convert`);
    return;
  }
  const MCR = require('monocart-coverage-reports');
  const mcr = MCR(buildOptions());
  let added = 0;
  for (const f of dumps) {
    try {
      const entries = JSON.parse(fs.readFileSync(f, 'utf8'));
      if (Array.isArray(entries) && entries.length) {
        await mcr.add(entries);
        added++;
      }
    } catch (e) {
      console.warn(`[e2e-coverage] skip ${f}: ${e.message}`);
    }
  }
  if (!added) {
    console.warn('[e2e-coverage] no valid dumps added');
    return;
  }
  await mcr.generate();
}

if (require.main === module) {
  main().catch(e => console.warn(`[e2e-coverage] fatal (ignored): ${e.message}`));
}

module.exports = {buildOptions, listDumps, OUT_DIR, V8_DIR};

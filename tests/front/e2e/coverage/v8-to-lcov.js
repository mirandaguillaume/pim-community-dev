/**
 * Convert raw Playwright V8 JS-coverage dumps (written per-test by the coverage
 * fixture) into a single merged lcov report, mapping bundled code back to the
 * original PIM sources (src/** and public/bundles/**) via the on-disk rspack
 * source maps (devtool:'source-map', assets under public/dist).
 *
 * Best-effort: any per-entry failure is logged and skipped; the process always
 * exits 0 so it can never fail the E2E job.
 *
 * Usage: node tests/front/e2e/coverage/v8-to-lcov.js
 *   IN : coverage-v8/<shard>/*.json   (arrays of {url, source, functions})
 *   OUT: coverage-e2e/lcov.info
 */
const fs = require('fs');
const path = require('path');
// The istanbul deps are lazy-required INSIDE the functions that use them, so the
// pure helpers (urlToDiskPath/keepSource/normalizeSource) — and the node check that
// imports them — load even when v8-to-istanbul et al. are not installed.

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const V8_DIR = path.join(REPO_ROOT, 'coverage-v8');
const OUT_DIR = path.join(REPO_ROOT, 'coverage-e2e');

/**
 * Map a V8 script `url` (served by the PIM app) to its built file on disk.
 * Only same-origin http(s) asset URLs resolve; anonymous/inline scripts return null.
 */
function urlToDiskPath(url, repoRoot) {
  if (typeof url !== 'string' || !/^https?:\/\//.test(url)) return null;
  let pathname;
  try {
    pathname = new URL(url).pathname;
  } catch (e) {
    return null;
  }
  if (!pathname || pathname === '/' || !/\.[cm]?js$/.test(pathname)) return null;
  return path.join(repoRoot, 'public', pathname.replace(/^\/+/, ''));
}

/**
 * Keep only original PIM sources: src/** and public/bundles/** (legacy AMD).
 * Drop node_modules, vendor, the rspack runtime, and anything else.
 */
function keepSource(sourcePath) {
  const p = sourcePath.replace(/\\/g, '/');
  if (/\/node_modules\//.test(p) || /(^|\/)vendor\//.test(p)) return false;
  if (/\/webpack\//.test(p) || /webpack\/(bootstrap|runtime)/.test(p)) return false;
  return /\/src\//.test(p) || /\/public\/bundles\//.test(p);
}

/** Normalize a source-map source path to a repo-relative path Codecov understands. */
function normalizeSource(sourcePath) {
  let p = sourcePath
    .replace(/\\/g, '/')
    .replace(/^webpack:\/\/[^/]*\//, '')
    .replace(/^\.\//, '');
  const idx = p.search(/(^|\/)(src|public)\//);
  if (idx >= 0) p = p.slice(p.indexOf(p.match(/(src|public)\//)[0]));
  return p;
}

/** Convert one V8 dump array into istanbul file-coverage objects, filtered + normalized. */
async function convertDump(entries, repoRoot) {
  const v8toIstanbul = require('v8-to-istanbul');
  const out = [];
  for (const entry of entries || []) {
    const diskPath = urlToDiskPath(entry.url, repoRoot);
    if (!diskPath || !fs.existsSync(diskPath)) continue;
    try {
      const converter = v8toIstanbul(diskPath, 0, {source: entry.source});
      await converter.load();
      converter.applyCoverage(entry.functions || []);
      const istanbul = converter.toIstanbul();
      for (const [file, fileCov] of Object.entries(istanbul)) {
        if (!keepSource(file)) continue;
        fileCov.path = normalizeSource(file);
        out.push(fileCov);
      }
    } catch (e) {
      console.warn(`[v8-to-lcov] skip ${entry.url}: ${e.message}`);
    }
  }
  return out;
}

async function main() {
  const libCoverage = require('istanbul-lib-coverage');
  const libReport = require('istanbul-lib-report');
  const reports = require('istanbul-reports');
  const map = libCoverage.createCoverageMap({});
  if (!fs.existsSync(V8_DIR)) {
    console.warn(`[v8-to-lcov] no ${V8_DIR}; nothing to convert`);
    return;
  }
  const files = [];
  (function walk(dir) {
    for (const name of fs.readdirSync(dir)) {
      const full = path.join(dir, name);
      if (fs.statSync(full).isDirectory()) walk(full);
      else if (name.endsWith('.json')) files.push(full);
    }
  })(V8_DIR);

  for (const f of files) {
    let entries;
    try {
      entries = JSON.parse(fs.readFileSync(f, 'utf8'));
    } catch (e) {
      console.warn(`[v8-to-lcov] bad json ${f}: ${e.message}`);
      continue;
    }
    for (const fileCov of await convertDump(entries, REPO_ROOT)) {
      // Merge unions execution counts for the same file across tests/scripts.
      map.merge({[fileCov.path]: fileCov});
    }
  }

  fs.mkdirSync(OUT_DIR, {recursive: true});
  const context = libReport.createContext({dir: OUT_DIR, coverageMap: map});
  reports.create('lcovonly', {file: 'lcov.info'}).execute(context);
  const summary = map.getCoverageSummary();
  console.log(
    `[v8-to-lcov] ${map.files().length} files, lines ${summary.lines.pct}% → ${path.join(OUT_DIR, 'lcov.info')}`
  );
}

if (require.main === module) {
  main().catch(e => console.warn(`[v8-to-lcov] fatal (ignored): ${e.message}`));
}

module.exports = {urlToDiskPath, keepSource, normalizeSource, convertDump};

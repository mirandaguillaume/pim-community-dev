/**
 * Joins per-test PHP and JS coverage into the migration inventory.
 *
 * Two views are written. `scenarios.json` answers "what does this test exercise", which is what you
 * need to reproduce a Behat scenario in Playwright. `files.json` is the inverse and answers the
 * actual migration question: when a file's last remaining Behat scenario has moved, that file's
 * coverage is safe to consider migrated.
 *
 * JS side: each per-test V8 dump goes through monocart individually, which unpacks the original
 * sources from the rspack source maps (devtool:'source-map'). One monocart pass per test is slower
 * than one pass overall, but this job runs on demand, so clarity wins over speed.
 *
 * PHP keys are the raw, human-readable scenario id (`<feature>:<line>`). JS dump filenames are that
 * same id passed through Task 6's `sanitise()` (a scenario id is not a safe filename). `join()`
 * matches both sides on the sanitised form but keeps the raw PHP id as the output key wherever PHP
 * knows the scenario, so `scenarios.json` stays readable instead of turning into a lookup table.
 *
 * Best-effort: any single test's failure warns and is skipped; the process still exits 0.
 */
const fs = require('fs');
const path = require('path');
const {buildOptions, listDumps} = require('./e2e-coverage-report');

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const OUT_DIR = path.join(REPO_ROOT, 'docs/coverage-inventory');

/** Match Task 6's filename transform so PHP and JS keys line up. Idempotent. */
function sanitise(testId) {
  return String(testId)
    .replace(/[^0-9a-z]+/gi, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Sanitised id -> raw id, warning (not throwing) when two distinct raw ids collide after
 * sanitising -- without the warning, the second one would silently overwrite the first and its
 * coverage would vanish from the join with no signal.
 */
function bySanitised(map, label) {
  const out = {};
  for (const rawId of Object.keys(map)) {
    const key = sanitise(rawId);
    if (out[key] !== undefined && out[key] !== rawId) {
      console.warn(
        `[inventory] WARNING: ${label} ids "${out[key]}" and "${rawId}" both sanitise to "${key}" -- the join keeps only the last one`
      );
    }
    out[key] = rawId;
  }
  return out;
}

/**
 * @param {Record<string, Record<string, number[]>>} php keyed by the raw `<feature>:<line>` id.
 * @param {Record<string, Record<string, number[]>>} js keyed by the sanitised id (dump filename
 *   minus extension).
 */
function join(php, js) {
  // Sanitised id -> raw id, one lookup per side. Applying sanitise() to an already-sanitised JS
  // key is a no-op (it's idempotent), so this same lookup works whether an id arrives raw or
  // already sanitised.
  const rawPhpBySanitised = bySanitised(php, 'PHP');
  const rawJsBySanitised = bySanitised(js, 'JS');

  const seen = new Set();
  const out = {};
  for (const id of [...Object.keys(php), ...Object.keys(js)]) {
    const sanitisedId = sanitise(id);
    if (seen.has(sanitisedId)) continue;
    seen.add(sanitisedId);

    const rawPhpId = rawPhpBySanitised[sanitisedId];
    const rawJsId = rawJsBySanitised[sanitisedId];

    out[rawPhpId !== undefined ? rawPhpId : id] = {
      php: (rawPhpId !== undefined ? php[rawPhpId] : undefined) || {},
      js: (rawJsId !== undefined ? js[rawJsId] : undefined) || {},
    };
  }

  return Object.fromEntries(Object.entries(out).sort(([a], [b]) => a.localeCompare(b)));
}

/** scenario -> code becomes code -> scenarios. */
function invert(scenarios) {
  const files = {};
  for (const [test, sides] of Object.entries(scenarios)) {
    for (const map of [sides.php, sides.js]) {
      for (const file of Object.keys(map)) {
        (files[file] ||= []).push(test);
      }
    }
  }
  for (const file of Object.keys(files)) {
    files[file] = [...new Set(files[file])].sort();
  }
  return Object.fromEntries(Object.entries(files).sort(([a], [b]) => a.localeCompare(b)));
}

/** Run one per-test V8 dump through monocart and return {file: [lines]} for covered lines. */
async function jsCoverageForDump(dumpFile) {
  const MCR = require('monocart-coverage-reports');
  // Each dump gets its own outputDir, derived purely from the dump's test id. monocart stages
  // raw coverage under outputDir between add() and generate(); sharing one directory across
  // instances would let dump N+1 pick up dump N's staged entries and accumulate coverage across
  // scenarios instead of reporting each one in isolation.
  const mcr = MCR({
    ...buildOptions(),
    outputDir: path.join(REPO_ROOT, 'var/tmp/mcr-inventory', path.basename(dumpFile, '.json')),
    reports: ['none'],
  });

  await mcr.add(JSON.parse(fs.readFileSync(dumpFile, 'utf8')));
  const results = await mcr.generate();
  const files = (results && results.files) || [];

  // Unverified locally: monocart isn't installed in this environment (see the check file), so
  // `results.files[].data.lines` is an assumption carried over from e2e-coverage-report.js, not
  // something this branch can confirm. A silently empty inventory is the failure mode most likely
  // to go unnoticed, so surface a wrong shape loudly instead of guessing at a fallback.
  if (!files.length) {
    console.warn(`[inventory] WARNING: 0 files from ${dumpFile} — check monocart's result shape`);
  }

  const out = {};
  for (const file of files) {
    const covered = Object.entries((file.data && file.data.lines) || {})
      .filter(([, hits]) => hits > 0)
      .map(([line]) => Number(line))
      .sort((a, b) => a - b);

    if (covered.length) out[file.sourcePath] = covered;
  }

  return out;
}

async function main() {
  const phpFile = process.argv[2];
  const v8Dir = process.argv[3];

  const php = phpFile && fs.existsSync(phpFile) ? JSON.parse(fs.readFileSync(phpFile, 'utf8')) : {};
  if (!Object.keys(php).length) console.warn(`[inventory] WARNING: no PHP entries from ${phpFile}`);

  const js = {};
  const dumps = v8Dir ? listDumps(v8Dir) : [];
  if (!dumps.length) console.warn(`[inventory] WARNING: no JS dumps under ${v8Dir}`);

  for (const dumpFile of dumps) {
    const testId = path.basename(dumpFile, '.json');
    try {
      js[testId] = await jsCoverageForDump(dumpFile);
    } catch (e) {
      console.warn(`[inventory] skip JS ${dumpFile}: ${e.message}`);
    }
  }

  const scenarios = join(php, js);
  const files = invert(scenarios);

  fs.mkdirSync(OUT_DIR, {recursive: true});
  fs.writeFileSync(path.join(OUT_DIR, 'scenarios.json'), JSON.stringify(scenarios, null, 2) + '\n');
  fs.writeFileSync(path.join(OUT_DIR, 'files.json'), JSON.stringify(files, null, 2) + '\n');

  console.log(`[inventory] wrote ${Object.keys(scenarios).length} tests, ${Object.keys(files).length} files`);
}

if (require.main === module) {
  main().catch(e => console.warn(`[inventory] fatal (ignored): ${e.message}`));
}

module.exports = {join, invert, jsCoverageForDump, sanitise, OUT_DIR};

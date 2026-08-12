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
// Imported, not redefined: this used to be a byte-identical copy of Task 6's transform, duplicated
// with no import between the two files -- so a change to one silently split every scenario into a
// PHP-only and a JS-only entry at the join below, with nothing in CI to catch it (the four
// `.check.js` files existed but nothing ran them either; see ci.yml's "Run coverage-inventory join
// checks" step). behat-cdp-coverage.js is the producer that NAMES the dump with this transform and
// has no dependency on this file, so it's the sane direction: this file (a consumer) depends on it,
// not the other way around.
const {sanitise} = require('./behat-cdp-coverage');

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const OUT_DIR = path.join(REPO_ROOT, 'docs/coverage-inventory');

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

/**
 * Drop the line numbers, keeping only WHICH files a scenario touched.
 *
 * Not a preference: `JSON.stringify` on the line-level structure throws `Invalid string length`.
 * Measured on the first run that collected both halves — one shard held 52 tests, 98,905 file
 * entries and 419,281 line numbers in 16.9 MB, so 612 scenarios come to ~199 MB unindented and
 * roughly double that with `null, 2`, against V8's ~512 MB ceiling for a single string. The design
 * doc pre-authorised this fallback as a remedy for unwieldy diffs; it turns out to be the only way
 * the file can be produced at all.
 *
 * Nothing is lost that this artifact is for. The question it answers is "which scenarios protect
 * this file", and `files.json` answers it without a single line number. The line detail still ships
 * in the per-shard CI artifacts (`inventory-php-<shard>.json` and `coverage-v8/`) for anyone
 * digging into one specific case.
 */
function toFileLevel(scenarios) {
  const out = {};
  for (const [test, sides] of Object.entries(scenarios)) {
    out[test] = {
      php: Object.keys(sides.php || {}).sort(),
      js: Object.keys(sides.js || {}).sort(),
    };
  }
  return out;
}

/** A file this many scenarios touch is framework boot, not a migration signal. */
const SUBSTRATE_RATIO = 0.9;

/**
 * Split off the common substrate: files exercised by ~every scenario.
 *
 * Every Behat scenario drives a full HTTP stack, so kernel boot, security, ORM and serialisation
 * appear in all of them. Measured on run 31374441296: of 2965 files, 1701 (57%) were touched by 90%
 * or more of the 618 scenarios, while only 317 (11%) were touched by five or fewer.
 *
 * Keeping them is worse than useless twice over. They are what makes the artifact enormous -- they
 * account for the bulk of the 1,187,960 paths that turned a "5-10 MB" file-level inventory into
 * 109 MB. And they drown the signal: a file listing 600 scenarios tells you nothing about which
 * Behat test protects it, which is the only question this inventory exists to answer.
 *
 * They are written to substrate.json with their counts rather than dropped silently -- an exclusion
 * nobody can see is indistinguishable from a collection bug, which is the failure mode this whole
 * pipeline is built to avoid.
 */
function splitSubstrate(scenarios) {
  const counts = {};
  for (const sides of Object.values(scenarios)) {
    for (const list of [sides.php, sides.js]) {
      for (const file of list) counts[file] = (counts[file] || 0) + 1;
    }
  }

  const total = Object.keys(scenarios).length;
  const floor = Math.ceil(total * SUBSTRATE_RATIO);
  const substrate = {};
  for (const [file, n] of Object.entries(counts)) {
    if (n >= floor) substrate[file] = n;
  }

  const kept = {};
  for (const [test, sides] of Object.entries(scenarios)) {
    kept[test] = {
      php: sides.php.filter(f => substrate[f] === undefined),
      js: sides.js.filter(f => substrate[f] === undefined),
    };
  }

  return {kept, substrate, floor, total};
}

/** scenario -> code becomes code -> scenarios. Consumes the file-level shape from toFileLevel(). */
function invert(scenarios) {
  const files = {};
  for (const [test, sides] of Object.entries(scenarios)) {
    for (const list of [sides.php, sides.js]) {
      // Arrays here, not maps: Object.keys() on an array would yield "0", "1", … as filenames.
      for (const file of Array.isArray(list) ? list : Object.keys(list || {})) {
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

  // File-level from here on: the line-level structure cannot be JSON.stringify'd at this scale.
  const all = toFileLevel(join(php, js));
  const {kept, substrate, floor, total} = splitSubstrate(all);
  const files = invert(kept);

  fs.mkdirSync(OUT_DIR, {recursive: true});
  fs.writeFileSync(path.join(OUT_DIR, 'scenarios.json'), JSON.stringify(kept, null, 2) + '\n');
  fs.writeFileSync(path.join(OUT_DIR, 'files.json'), JSON.stringify(files, null, 2) + '\n');
  fs.writeFileSync(
    path.join(OUT_DIR, 'substrate.json'),
    JSON.stringify(
      {
        note: `files touched by at least ${floor} of ${total} scenarios (${SUBSTRATE_RATIO * 100}%); excluded from scenarios.json and files.json because they carry no migration signal`,
        threshold: floor,
        scenarios: total,
        files: Object.fromEntries(Object.entries(substrate).sort(([, a], [, b]) => b - a)),
      },
      null,
      2
    ) + '\n'
  );

  console.log(
    `[inventory] wrote ${Object.keys(kept).length} tests, ${Object.keys(files).length} files ` +
      `(${Object.keys(substrate).length} common-substrate files excluded, seen by >= ${floor}/${total} scenarios)`
  );
}

if (require.main === module) {
  main().catch(e => console.warn(`[inventory] fatal (ignored): ${e.message}`));
}

module.exports = {join, toFileLevel, splitSubstrate, invert, jsCoverageForDump, sanitise, OUT_DIR};

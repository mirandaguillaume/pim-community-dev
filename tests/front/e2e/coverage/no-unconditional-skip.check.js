/**
 * A Playwright spec must FAIL when a fixture is missing, never skip: a skipped spec is green and
 * protects nothing. Unconditional `test.skip(true, …)` calls had accumulated to 18 across six specs,
 * silently disabling whole scenarios that the Behat→Playwright migration had been relied on to replace.
 *
 * Conditional skips are NOT matched and stay legitimate — `test.skip(indicatorCount === 0, …)` in
 * filter-attributes.spec.ts is a real data-dependent capability check.
 *
 * Runs in CI with no workflow change: ci.yml's lint-front job loops over
 * tests/front/e2e/coverage/*.check.js, and lint-front is a ci-success dependency.
 *
 * Run: node tests/front/e2e/coverage/no-unconditional-skip.check.js
 */
const assert = require('assert');
const fs = require('fs');
const path = require('path');

const E2E_ROOT = path.join(__dirname, '..');
// Built from parts so this file never matches its own pattern.
const UNCONDITIONAL = new RegExp('test' + '\\.skip\\(\\s*true\\b');

function specFiles(dir) {
  return fs.readdirSync(dir, {withFileTypes: true}).flatMap(entry => {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) return specFiles(full);
    return entry.isFile() && entry.name.endsWith('.spec.ts') ? [full] : [];
  });
}

/**
 * Empty, and it must stay that way. It briefly held the two import specs while their consumer
 * probes were converted; adding a file back is not an acceptable way to pass this check.
 */
const NOT_YET_CONVERTED = [];

const offenders = [];
for (const file of specFiles(E2E_ROOT)) {
  fs.readFileSync(file, 'utf8')
    .split('\n')
    .forEach((line, i) => {
      const relative = path.relative(E2E_ROOT, file);
      if (UNCONDITIONAL.test(line) && !NOT_YET_CONVERTED.includes(relative)) {
        offenders.push(`${relative}:${i + 1}: ${line.trim()}`);
      }
    });
}

assert.deepStrictEqual(
  offenders,
  [],
  `Unconditional test.skip(true, …) found. A missing fixture must fail the spec, not skip it — ` +
    `assert it in beforeAll/beforeEach instead:\n  ${offenders.join('\n  ')}`
);

// A file that no longer offends must leave the list, or the list quietly becomes permission.
const stillNeeded = NOT_YET_CONVERTED.filter(relative => {
  const full = path.join(E2E_ROOT, relative);
  return fs.existsSync(full) && fs.readFileSync(full, 'utf8').split('\n').some(line => UNCONDITIONAL.test(line));
});
assert.deepStrictEqual(
  NOT_YET_CONVERTED.filter(f => !stillNeeded.includes(f)),
  [],
  'These files are listed as not-yet-converted but no longer contain an unconditional skip. ' +
    'Remove them from NOT_YET_CONVERTED.'
);

console.log(
  `no-unconditional-skip: OK (${specFiles(E2E_ROOT).length} spec files scanned, ` +
    `${NOT_YET_CONVERTED.length} still to convert)`
);

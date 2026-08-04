/**
 * Node-runnable checks for the inventory join.
 * Run: node tests/front/e2e/coverage/build-inventory.check.js
 */
const assert = require('assert');
const {join, invert, sanitise} = require('./build-inventory');

const php = {
  'a.feature:1': {'src/A.php': [3, 5]},
  'b.feature:2': {'src/A.php': [3]},
};
const js = {
  'a.feature:1': {'src/front/x.ts': [10]},
};

const scenarios = join(php, js);

// Every test from either side appears, with both keys always present.
assert.deepStrictEqual(Object.keys(scenarios), ['a.feature:1', 'b.feature:2']);
assert.deepStrictEqual(scenarios['a.feature:1'].php, {'src/A.php': [3, 5]});
assert.deepStrictEqual(scenarios['a.feature:1'].js, {'src/front/x.ts': [10]});
assert.deepStrictEqual(scenarios['b.feature:2'].js, {}, 'a test with no JS still gets an empty map');

// The inverse view: which tests cover a file. This is the one that answers the migration
// question -- when a file's last Behat scenario is gone, its coverage has moved.
const files = invert(scenarios);
assert.deepStrictEqual(files['src/A.php'], ['a.feature:1', 'b.feature:2']);
assert.deepStrictEqual(files['src/front/x.ts'], ['a.feature:1']);

// sanitise() must be idempotent -- join() relies on that to match a raw PHP id against an
// already-sanitised JS id through the same lookup.
assert.strictEqual(
  sanitise('tests/legacy/features/foo.feature:23'),
  sanitise(sanitise('tests/legacy/features/foo.feature:23')),
  'sanitise is idempotent'
);

// The actual production join: a raw PHP key and its already-sanitised JS counterpart (the form
// Task 6's dump filenames use) must collapse into exactly one entry, keyed by the raw id -- not
// two separate entries under two different keys.
const joined = join(
  {'tests/legacy/features/foo.feature:23': {'src/A.php': [1]}},
  {'tests-legacy-features-foo-feature-23': {'src/front/x.ts': [2]}}
);
assert.strictEqual(Object.keys(joined).length, 1, 'a scenario present on both sides yields exactly one entry');
assert.deepStrictEqual(
  Object.keys(joined),
  ['tests/legacy/features/foo.feature:23'],
  'the entry is keyed by the raw, human-readable PHP id'
);
assert.deepStrictEqual(joined['tests/legacy/features/foo.feature:23'].php, {'src/A.php': [1]});
assert.deepStrictEqual(joined['tests/legacy/features/foo.feature:23'].js, {'src/front/x.ts': [2]});

// A sanitise collision (two distinct raw ids that sanitise to the same string) must warn instead
// of silently dropping one scenario's coverage. console.warn is a global seam, so it's captured
// here rather than restructuring join() around testability.
{
  const warnings = [];
  const originalWarn = console.warn;
  console.warn = message => warnings.push(message);
  try {
    join({'a/b.feature:1': {'src/A.php': [1]}, 'a-b.feature:1': {'src/B.php': [2]}}, {});
  } finally {
    console.warn = originalWarn;
  }
  assert.ok(
    warnings.some(w => /both sanitise/.test(w) && w.includes('a/b.feature:1') && w.includes('a-b.feature:1')),
    'a sanitise collision is warned, not silently dropped'
  );
}

console.log('build-inventory checks passed');

/**
 * Node-runnable checks for the inventory join.
 * Run: node tests/front/e2e/coverage/build-inventory.check.js
 */
const assert = require('assert');
const fs = require('fs');
const os = require('os');
const path = require('path');
const {join, toFileLevel, splitSubstrate, invert, attachSourceMap, sanitise} = require('./build-inventory');

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
// What actually gets written is the file-level reduction: JSON.stringify on the line-level
// structure throws `Invalid string length` at real scale (~199 MB of PHP alone for 612 scenarios,
// against V8's ~512 MB ceiling). toFileLevel() is what main() feeds to invert() and to disk.
const fileLevel = toFileLevel(scenarios);
assert.deepStrictEqual(fileLevel['a.feature:1'].php, ['src/A.php'], 'line numbers are dropped, files kept');
assert.deepStrictEqual(fileLevel['a.feature:1'].js, ['src/front/x.ts']);
assert.deepStrictEqual(fileLevel['b.feature:2'].js, [], 'a scenario with no JS keeps an empty list');
assert.ok(Array.isArray(fileLevel['a.feature:1'].php), 'arrays, not maps — the gate counts their keys');

const files = invert(fileLevel);
assert.deepStrictEqual(files['src/A.php'], ['a.feature:1', 'b.feature:2']);
assert.deepStrictEqual(files['src/front/x.ts'], ['a.feature:1']);

// invert() must read the ARRAY, not its indices: Object.keys(['src/A.php']) is ['0'], so a
// regression here would fill files.json with "0", "1", … instead of paths — and still look sorted.
assert.ok(!Object.keys(files).some(f => /^\d+$/.test(f)), 'no numeric keys leaked from array indices');

// The common substrate -- files ~every scenario touches -- is split out, because it is both what
// makes the artifact enormous and what drowns the signal. Ten scenarios, `boot.php` in all ten and
// `Rare.php` in one: at a 90% floor boot.php goes, Rare.php stays.
{
  const many = {};
  for (let i = 0; i < 10; i++) {
    many[`f.feature:${i}`] = {php: i === 0 ? ['src/boot.php', 'src/Rare.php'] : ['src/boot.php'], js: []};
  }
  const {kept, substrate, floor, total} = splitSubstrate(many);

  assert.strictEqual(total, 10);
  assert.strictEqual(floor, 9, 'floor is ceil(total * 0.9)');
  assert.deepStrictEqual(Object.keys(substrate), ['src/boot.php'], 'only the ubiquitous file is substrate');
  assert.strictEqual(substrate['src/boot.php'], 10, 'substrate.json keeps the count, so the exclusion is auditable');
  assert.deepStrictEqual(kept['f.feature:0'].php, ['src/Rare.php'], 'the discriminating file survives');
  assert.deepStrictEqual(kept['f.feature:1'].php, [], 'a scenario that only touched substrate keeps an empty list');

  // And the inverse view must no longer mention it at all -- a file listing every scenario is
  // exactly the entry that answers nothing about what to migrate.
  assert.strictEqual(invert(kept)['src/boot.php'], undefined, 'substrate is absent from files.json');
  assert.deepStrictEqual(invert(kept)['src/Rare.php'], ['f.feature:0']);
}

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

// attachSourceMap() is what stops the JS half degrading to bundle urls. The dump's sourceMappingURL
// is relative to an http origin, so it resolves to http://httpd/dist/vendor.min.js.map -- not
// something monocart can read. The url's PATHNAME is what maps onto public/, and the cache-busting
// query must be dropped along the way.
{
  const root = path.resolve(__dirname, '../../../..');
  const dir = path.join(root, 'public', 'dist');
  const mapFile = path.join(dir, 'probe-check.js.map');
  const existed = fs.existsSync(dir);
  fs.mkdirSync(dir, {recursive: true});
  fs.writeFileSync(mapFile, JSON.stringify({version: 3, sources: ['src/probe.ts'], mappings: ''}));

  try {
    const entry = {
      url: 'http://httpd/dist/probe-check.js?eb0a191797624dd3a48fa681d3061212',
      source: 'console.log(1)\n//# sourceMappingURL=probe-check.js.map\n',
    };
    attachSourceMap(entry);
    assert.ok(entry.sourceMap, 'the map is found despite the cache-busting query string');
    assert.deepStrictEqual(entry.sourceMap.sources, ['src/probe.ts']);

    // An entry that already carries one is left alone, and a missing map warns without throwing.
    const already = {url: entry.url, source: entry.source, sourceMap: {version: 3, sources: ['kept']}};
    attachSourceMap(already);
    assert.deepStrictEqual(already.sourceMap.sources, ['kept'], 'an existing sourceMap is not overwritten');

    const warnings = [];
    const realWarn = console.warn;
    console.warn = m => warnings.push(m);
    try {
      const missing = {url: 'http://httpd/dist/nope.js', source: '//# sourceMappingURL=nope.js.map'};
      attachSourceMap(missing);
      assert.strictEqual(missing.sourceMap, undefined, 'a missing map leaves the entry untouched');
    } finally {
      console.warn = realWarn;
    }
    assert.ok(
      warnings.some(w => /no source map at/.test(w)),
      'a missing map warns -- silence here is what shipped bundle urls as coverage'
    );

    // No url to anchor the lookup: must not throw.
    const anon = {url: '', source: '//# sourceMappingURL=x.map'};
    attachSourceMap(anon);
    assert.strictEqual(anon.sourceMap, undefined);
  } finally {
    fs.unlinkSync(mapFile);
    if (!existed) fs.rmSync(dir, {recursive: true, force: true});
  }
}

console.log('build-inventory checks passed');

const assert = require('assert');
const {urlToDiskPath, keepSource} = require('./v8-to-lcov');

// urlToDiskPath: strip the served origin, resolve under public/
assert.strictEqual(
  urlToDiskPath('http://localhost:8080/dist/pim.js', '/repo'),
  '/repo/public/dist/pim.js',
  'served /dist/* maps to public/dist/*'
);
assert.strictEqual(
  urlToDiskPath('http://localhost:8080/js/require-paths.js', '/repo'),
  '/repo/public/js/require-paths.js',
  'served /js/* maps to public/js/*'
);
assert.strictEqual(urlToDiskPath('http://localhost:8080/', '/repo'), null, 'the bare document URL has no disk asset');
assert.strictEqual(urlToDiskPath('inline-script-1', '/repo'), null, 'anonymous/inline scripts have no disk path');

// keepSource: keep src/** and public/bundles/**, drop everything else
assert.strictEqual(keepSource('webpack://pim/./src/Foo/Bar.tsx'), true, 'keep src tsx');
assert.strictEqual(keepSource('webpack://pim/./public/bundles/pimui/js/x.js'), true, 'keep legacy bundles');
assert.strictEqual(keepSource('webpack://pim/./node_modules/react/index.js'), false, 'drop node_modules');
assert.strictEqual(keepSource('webpack://pim/webpack/bootstrap'), false, 'drop rspack runtime');
assert.strictEqual(keepSource('/vendor/symfony/foo.js'), false, 'drop vendor');

console.log('v8-to-lcov unit checks passed');

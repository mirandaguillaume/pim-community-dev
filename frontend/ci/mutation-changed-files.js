#!/usr/bin/env node
/**
 * Print the changed front-end .ts/.tsx files that carry a REAL logic change
 * between origin/master and HEAD — i.e. the files worth mutation-testing.
 *
 * Why this exists
 * ---------------
 * `mutation-testing-front` mutates the changed source files. A purely
 * MECHANICAL change (reformatting, or a module-syntax migration such as
 * `require()` -> `import`) cannot alter runtime behaviour, so mutating it only
 * scores pre-existing, often-untested logic and craters the mutation score
 * (the AMD->ESM migration touched ~170 legacy/workspace files with no unit
 * tests -> score 4%). The old bash heuristic stripped whitespace only, so it
 * still treated `require`->`import` as a logic change.
 *
 * This script normalises BOTH revisions of each file by removing module-syntax
 * lines (import / export / require / module.exports / the __pimInterop helper)
 * and all whitespace, then compares the remaining body. Files whose body is
 * identical are pure syntax/format conversions and are dropped. Type-only
 * declaration files (*.d.ts) have no mutable runtime logic and are dropped too.
 *
 * Output: newline-separated list of files with a real logic change (may be
 * empty -> caller should skip the mutation run).
 *
 * Usage: node frontend/ci/mutation-changed-files.js
 */
'use strict';
const {execFileSync} = require('child_process');

// No shell: arguments are passed directly to git, so a filename containing
// shell metacharacters can never be interpreted.
const git = args => execFileSync('git', args, {encoding: 'utf8', maxBuffer: 64 * 1024 * 1024});

const changed = git(['diff', '--diff-filter=AM', '--name-only', 'origin/master...HEAD', '--', '*.ts', '*.tsx'])
  .split('\n')
  .map(s => s.trim())
  .filter(Boolean)
  .filter(f => /^(front-packages|components|src)\//.test(f))
  .filter(f => !/\.(test|spec|unit|visual)\.|__tests__|__mocks__|stories|\.storybook\//.test(f))
  .filter(f => !/\.d\.ts$/.test(f)) // type-only: nothing to mutate
  .filter(f => !/^src\/Akeneo\/Category\//.test(f));

// Lines that are pure module wiring — present/changed by a syntax migration but
// never the thing mutation testing should score.
const isModuleSyntax = line => {
  const t = line.trim();
  return (
    t === '' ||
    t === "'use strict';" ||
    t === '"use strict";' ||
    /^import\b/.test(t) ||
    /^export\s+(default|\*|\{|type\b|interface\b|const\b|function\b|class\b)/.test(t) ||
    /^export\s*=/.test(t) ||
    /^module\.exports\s*=/.test(t) ||
    /^(var|const|let)\s+[\w${},*\s]+=\s*require\(/.test(t) ||
    /^(var|const|let)\s+\w+\s*=\s*__pimInterop\(\s*require\(/.test(t) ||
    /^require\(/.test(t) ||
    // the injected interop helper (single-line or its closing brace handled by ws-strip)
    /^function __pimInterop\(m\)/.test(t) ||
    /^return m && m\.__esModule/.test(t)
  );
};

const body = src =>
  src
    .split('\n')
    .filter(l => !isModuleSyntax(l))
    .join('')
    .replace(/\s+/g, '');

const real = [];
for (const f of changed) {
  let oldSrc = '';
  let newSrc = '';
  try {
    oldSrc = git(['show', `origin/master:${f}`]);
  } catch {
    real.push(f); // new file -> real change
    continue;
  }
  try {
    newSrc = git(['show', `HEAD:${f}`]);
  } catch {
    continue; // deleted -> nothing to mutate
  }
  if (body(oldSrc) !== body(newSrc)) real.push(f);
}

process.stdout.write(real.join('\n') + (real.length ? '\n' : ''));

/* eslint-env node, es2020 */
/**
 * jscodeshift codemod: CJS (the amd-to-cjs.js output) → native ESM.
 *
 * Phase B3 STEP 5 — run ONLY after `amd: {}` has been removed from rspack.config.js
 * and there are no remaining `define()` / AMD `require([...])` consumers. This is the
 * exact inverse of amd-to-cjs.js.
 *
 * Transforms (top-level only):
 *   function __pimInterop(m) {...}            → (removed; ESM import does interop natively)
 *   var Foo = __pimInterop(require('dep'));    → import Foo from 'dep';
 *   var {A, B} = require('dep');               → import { A, B } from 'dep';
 *   var {A: b} = require('dep');               → import { A as b } from 'dep';
 *   require('side-effect');                    → import 'side-effect';
 *   module.exports = X;                        → export default X;
 *   'use strict';                              → (removed; ESM is always strict)
 *
 * WHY this maps cleanly:
 *   __pimInterop returns `m.default` for an __esModule dep, else `m` — i.e. default
 *   interop, which is precisely what `import Foo from 'dep'` does (incl. webpack's
 *   CJS→default interop). A raw destructure reads named members off the namespace =
 *   `import { A } from 'dep'`. So amd-to-cjs output round-trips to ESM faithfully.
 *
 * Throws (→ manual migration) on shapes it will not silently mistranslate:
 *   - named CJS exports (`module.exports.foo = …`, `exports.foo = …`)
 *   - more than one `module.exports =`
 *   - `var X = require('dep')` WITHOUT __pimInterop (ambiguous: default vs namespace)
 *   - any `require()` left after the top-level pass (nested require → cannot be a static import)
 *
 * Usage (DO NOT run before amd:{} is removed):
 *   node_modules/.bin/jscodeshift -t frontend/codemods/cjs-to-esm.js <path> --parser=babel
 */

const INTEROP = '__pimInterop';

function isStringLiteral(node) {
  return node && (node.type === 'StringLiteral' || (node.type === 'Literal' && typeof node.value === 'string'));
}
function isRequireCall(node) {
  return (
    node &&
    node.type === 'CallExpression' &&
    node.callee.type === 'Identifier' &&
    node.callee.name === 'require' &&
    node.arguments.length === 1 &&
    isStringLiteral(node.arguments[0])
  );
}
function isInteropRequire(node) {
  return (
    node &&
    node.type === 'CallExpression' &&
    node.callee.type === 'Identifier' &&
    node.callee.name === INTEROP &&
    node.arguments.length === 1 &&
    isRequireCall(node.arguments[0])
  );
}
function isModuleExportsAssign(node) {
  return (
    node &&
    node.type === 'AssignmentExpression' &&
    node.operator === '=' &&
    node.left.type === 'MemberExpression' &&
    !node.left.computed &&
    node.left.object.type === 'Identifier' &&
    node.left.object.name === 'module' &&
    node.left.property.type === 'Identifier' &&
    node.left.property.name === 'exports'
  );
}
function isNamedCjsExport(node) {
  if (!node || node.type !== 'AssignmentExpression' || node.left.type !== 'MemberExpression') return false;
  const l = node.left;
  // exports.foo = …
  if (l.object.type === 'Identifier' && l.object.name === 'exports') return true;
  // module.exports.foo = …
  return (
    l.object.type === 'MemberExpression' &&
    l.object.object.type === 'Identifier' &&
    l.object.object.name === 'module' &&
    l.object.property.type === 'Identifier' &&
    l.object.property.name === 'exports'
  );
}

module.exports = function transformer(file, api) {
  const j = api.jscodeshift;
  const root = j(file.source);
  const program = root.get().value.program;

  // Only touch files that are actually CJS (have require() or module.exports).
  const looksCjs =
    root.find(j.CallExpression, {callee: {type: 'Identifier', name: 'require'}}).size() > 0 ||
    root
      .find(j.AssignmentExpression)
      .filter(p => isModuleExportsAssign(p.node))
      .size() > 0;
  if (!looksCjs) return null;

  const imports = [];
  const rest = [];
  let exportCount = 0;

  for (const stmt of program.body) {
    // drop the injected __pimInterop helper
    if (stmt.type === 'FunctionDeclaration' && stmt.id && stmt.id.name === INTEROP) continue;

    // drop a leading 'use strict' directive (ESM is implicitly strict)
    if (
      stmt.type === 'ExpressionStatement' &&
      isStringLiteral(stmt.expression) &&
      stmt.expression.value === 'use strict'
    ) {
      continue;
    }

    // var X = __pimInterop(require('dep'))  |  var {..} = require('dep')  |  var X = require('dep')
    if (stmt.type === 'VariableDeclaration' && stmt.declarations.length === 1) {
      const d = stmt.declarations[0];
      const init = d.init;
      let source = null;
      let viaInterop = false;
      if (isInteropRequire(init)) {
        source = init.arguments[0].arguments[0].value;
        viaInterop = true;
      } else if (isRequireCall(init)) {
        source = init.arguments[0].value;
        viaInterop = false;
      }
      if (source !== null) {
        if (d.id.type === 'Identifier') {
          if (!viaInterop) {
            throw new Error(
              `${file.path}: 'var ${d.id.name} = require('${source}')' without ${INTEROP} is ambiguous ` +
                `(default vs namespace) — manual migration needed`
            );
          }
          imports.push(j.importDeclaration([j.importDefaultSpecifier(j.identifier(d.id.name))], j.literal(source)));
          continue;
        }
        if (d.id.type === 'ObjectPattern') {
          const specifiers = d.id.properties.map(prop => {
            // shorthand {A} → imported=local=A ; renamed {A: b} → imported=A, local=b
            const imported = j.identifier(prop.key.name);
            const local = j.identifier((prop.value && prop.value.name) || prop.key.name);
            return j.importSpecifier(imported, local);
          });
          imports.push(j.importDeclaration(specifiers, j.literal(source)));
          continue;
        }
        throw new Error(`${file.path}: unhandled require binding shape "${d.id.type}" — manual migration needed`);
      }
      // not a require declaration → keep as-is
      rest.push(stmt);
      continue;
    }

    if (stmt.type === 'ExpressionStatement') {
      // bare require('dep');  → import 'dep';
      if (isRequireCall(stmt.expression)) {
        imports.push(j.importDeclaration([], j.literal(stmt.expression.arguments[0].value)));
        continue;
      }
      // module.exports = X  → export default X;
      if (isModuleExportsAssign(stmt.expression)) {
        exportCount += 1;
        rest.push(j.exportDefaultDeclaration(stmt.expression.right));
        continue;
      }
      // named CJS export → refuse
      if (isNamedCjsExport(stmt.expression)) {
        throw new Error(
          `${file.path}: named CJS export (module.exports.X / exports.X) — needs manual ESM named export`
        );
      }
    }

    rest.push(stmt);
  }

  if (exportCount > 1) {
    throw new Error(`${file.path}: multiple module.exports assignments — manual migration needed`);
  }

  // GUARD-RAIL: no require() may survive (a nested require cannot become a static import).
  const newProgramBody = [...imports, ...rest];
  program.body = newProgramBody;
  const leftover = root.find(j.CallExpression, {callee: {type: 'Identifier', name: 'require'}}).size();
  if (leftover > 0) {
    throw new Error(
      `${file.path}: ${leftover} require() call(s) remain after conversion (nested / non-top-level) — ` +
        `cannot be turned into static imports, manual migration needed`
    );
  }

  return root.toSource({quote: 'single'});
};

module.exports.parser = 'babel';

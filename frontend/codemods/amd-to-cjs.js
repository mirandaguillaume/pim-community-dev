/* eslint-env node, es2020 */
/**
 * jscodeshift codemod: AMD define() → CJS require/module.exports
 *
 * Targets the canonical Akeneo PIM AMD pattern:
 *
 *   define(['dep1', 'dep2'], function (Alias1, Alias2) {
 *     return X;
 *   });
 *
 * Becomes:
 *
 *   function __pimInterop(m) { return m && m.__esModule && 'default' in m ? m.default : m; }
 *   var Alias1 = __pimInterop(require('dep1'));
 *   var Alias2 = __pimInterop(require('dep2'));
 *
 *   module.exports = X;
 *
 * WHY CJS AND NOT ESM `export default`?
 * --------------------------------------
 * The RSPack build runs with `amd: {}` enabled (mandatory while any define()
 * modules remain — it makes RSPack follow the AMD dependency graph). Under
 * native AMD parsing, a `define(['pim/foo'], fn)` consumer receives whatever
 * `__webpack_require__('pim/foo')` returns. For a module migrated to ESM
 * `export default X`, that is the NAMESPACE object `{ default: X, __esModule: true }`
 * — NOT X — and RSPack does NOT apply default-interop to AMD `define` deps. So
 * `X.method` is `undefined` at runtime (the "X is not a function" interop break
 * that took down the whole enrichment UI on the first ESM wave).
 *
 * Empirically verified (see docs/plans/.../phase-B-amd-to-esm.md):
 *   - AMD define consuming ESM `export default`  → namespace  → BREAK
 *   - AMD define consuming CJS `module.exports`   → raw value  → WORKS
 *   - ESM `import` consuming CJS `module.exports` → interop    → WORKS
 *
 * So CJS `module.exports = X` is consumable by BOTH the remaining AMD `define`
 * consumers AND new ESM `import` consumers — the only shape that works for a
 * mixed/incremental migration. It also matches how the existing TypeScript
 * modules (which AMD code already consumes successfully) export (`export =`).
 *
 * The `__pimInterop` helper wraps each `require()` so that THIS module's own
 * dependencies are resolved correctly regardless of whether the dependency is
 * still AMD (returns a value), already CJS (module.exports), a TS `export =`
 * (CJS), or a true ESM module (`{ default, __esModule }`). Raw `require()` does
 * not apply default-interop, so without the helper an ESM/TS-default dependency
 * would itself come back as a namespace. The helper is a no-op for non-ESM deps.
 *
 * This is the TRANSITIONAL target. Once every define() is gone and `amd: {}`
 * can be removed from rspack.config.js, a final mechanical pass converts
 * `require`/`module.exports` → `import`/`export default` for tree-shaking.
 *
 * Usage:
 *   node_modules/.bin/jscodeshift -t frontend/codemods/amd-to-cjs.js <path> --extensions=js
 *
 * Flags: --dry (no write), --print (stdout), --ignore-pattern=<glob>
 *
 * Not handled (flagged by exception or skipped):
 *   - __moduleConfig global injected by require.js (deferred to Phase B2.5)
 *   - define() without a dependency array
 *   - Multiple define() in the same file
 */

const INTEROP_HELPER = '__pimInterop';

module.exports = function transformer(file, api) {
  const j = api.jscodeshift;
  const root = j(file.source);

  const defineCalls = root.find(j.CallExpression, {
    callee: {type: 'Identifier', name: 'define'},
  });

  if (defineCalls.size() === 0) {
    return null; // not an AMD module
  }
  if (defineCalls.size() > 1) {
    throw new Error(`${file.path}: multiple define() calls — manual migration needed`);
  }

  const defineCall = defineCalls.get();
  const args = defineCall.node.arguments;

  // Two supported shapes:
  //   define([deps], factory)  — deps + factory
  //   define(factory)          — no-dependency-array form (Oro abstract-formatter etc.)
  let depArray;
  let factory;
  if (args.length === 2 && args[0].type === 'ArrayExpression') {
    depArray = args[0];
    factory = args[1];
  } else if (args.length === 1) {
    depArray = j.arrayExpression([]); // no deps → no require() statements
    factory = args[0];
  } else {
    throw new Error(`${file.path}: unsupported define() shape — manual migration needed`);
  }

  const factoryIsFn = factory && (factory.type === 'FunctionExpression' || factory.type === 'ArrowFunctionExpression');
  const factoryHasBlock = factoryIsFn && factory.body && factory.body.type === 'BlockStatement';
  if (!factoryHasBlock) {
    throw new Error(`${file.path}: unsupported define() factory — manual migration needed`);
  }

  // Which factory params are actually referenced in the body? AMD never tripped
  // no-unused-vars on define() params, so unused ones accumulated. For those we
  // emit a side-effect-only `require('dep');` (no binding → no lint error).
  const usedParamNames = new Set();
  j(factory.body)
    .find(j.Identifier)
    .forEach(p => {
      const parent = p.parent.node;
      const isReference =
        !(parent.type === 'VariableDeclarator' && parent.id === p.node) &&
        !(parent.type === 'FunctionDeclaration' && parent.id === p.node) &&
        !(parent.type === 'Property' && !parent.computed && parent.key === p.node) &&
        !(parent.type === 'MemberExpression' && !parent.computed && parent.property === p.node);
      if (isReference) usedParamNames.add(p.node.name);
    });

  // Build the require() statements.
  const requireStmts = [];
  let needsInterop = false;
  for (let i = 0; i < depArray.elements.length; i++) {
    const depNode = depArray.elements[i];
    if (!depNode || depNode.type !== 'Literal' || typeof depNode.value !== 'string') {
      throw new Error(`${file.path}: non-string dep at index ${i} — manual migration needed`);
    }
    const param = factory.params[i];
    const requireCall = j.callExpression(j.identifier('require'), [j.literal(depNode.value)]);
    if (param && param.type === 'ObjectPattern') {
      // Destructured (NAMED) import: `var { a, b } = require('dep');`
      // Destructure from the RAW require, NOT __pimInterop: named exports live on the
      // module namespace, but __pimInterop unwraps an `__esModule` dep to its `.default`
      // — where the named members do not exist — yielding `undefined` bindings. Raw
      // require keeps the namespace, mirroring the AMD `define([...], fn({a}))` semantics
      // (which read `.a` off the dependency value). Destructured params are bound by
      // construction (the names are explicit), so they are always emitted, never
      // collapsed to a side-effect require.
      requireStmts.push(j.variableDeclaration('var', [j.variableDeclarator(param, requireCall)]));
    } else if (param && param.type === 'Identifier' && usedParamNames.has(param.name)) {
      // Bound + used: `var Alias = __pimInterop(require('dep'));`
      needsInterop = true;
      requireStmts.push(
        j.variableDeclaration('var', [
          j.variableDeclarator(j.identifier(param.name), j.callExpression(j.identifier(INTEROP_HELPER), [requireCall])),
        ])
      );
    } else if (!param || param.type === 'Identifier') {
      // No param, OR an Identifier param genuinely unused in the body:
      // side-effect-only `require('dep');` (no binding → no ReferenceError, no lint).
      requireStmts.push(j.expressionStatement(requireCall));
    } else {
      // GUARD-RAIL: any other param shape (AssignmentPattern/default value,
      // ArrayPattern, RestElement, …) is NOT one we know how to bind. Emitting a
      // bare require() here would SILENTLY DROP the binding and create a runtime
      // ReferenceError — the datagrid-killer class of bug, where a dropped
      // QuickExportConfigurator binding blanked the whole grid. Fail loudly so the
      // file gets manual migration instead of a silent break.
      throw new Error(
        `${file.path}: unhandled factory param shape "${param.type}" at dep index ${i} ` +
          `('${depNode.value}') — manual migration needed (a bare require() would drop the binding)`
      );
    }
  }

  // GUARD-RAIL (defense in depth): every USED identifier param must have produced a
  // `var Alias = ...` binding. If the usage heuristic above ever regresses and a
  // referenced param is misclassified as unused, this catches it at migration time
  // instead of shipping a silent runtime ReferenceError.
  const boundNames = new Set(
    requireStmts
      .filter(s => s.type === 'VariableDeclaration' && s.declarations[0].id.type === 'Identifier')
      .map(s => s.declarations[0].id.name)
  );
  for (const param of factory.params) {
    if (param && param.type === 'Identifier' && usedParamNames.has(param.name) && !boundNames.has(param.name)) {
      throw new Error(
        `${file.path}: used binding "${param.name}" would be dropped — codemod invariant violated, manual review needed`
      );
    }
  }

  // `function __pimInterop(m) { return m && m.__esModule && 'default' in m ? m.default : m; }`
  // The `'default' in m` guard is essential: a TS/ESM module with NAMED exports
  // only (e.g. `export const getMissingRequiredFields`) is compiled with
  // `__esModule: true` but NO `default`, so unwrapping to `m.default` would yield
  // undefined. Such modules must be returned as the namespace so callers can read
  // their named members. Mirrors createModuleRegistry's runtime-require unwrap.
  const interopDecl = j.functionDeclaration(
    j.identifier(INTEROP_HELPER),
    [j.identifier('m')],
    j.blockStatement([
      j.returnStatement(
        j.conditionalExpression(
          j.logicalExpression(
            '&&',
            j.logicalExpression(
              '&&',
              j.identifier('m'),
              j.memberExpression(j.identifier('m'), j.identifier('__esModule'))
            ),
            j.binaryExpression('in', j.literal('default'), j.identifier('m'))
          ),
          j.memberExpression(j.identifier('m'), j.identifier('default')),
          j.identifier('m')
        )
      ),
    ])
  );

  // Convert the factory's TOP-LEVEL `return X;` → `module.exports = X;`.
  // Only the factory's own top-level return is rewritten; returns nested inside
  // methods/functions stay untouched (they are deeper in the AST, not in
  // factory.body.body).
  const newBodyStmts = [];
  for (const stmt of factory.body.body) {
    if (stmt.type === 'ReturnStatement' && stmt.argument) {
      newBodyStmts.push(
        j.expressionStatement(
          j.assignmentExpression(
            '=',
            j.memberExpression(j.identifier('module'), j.identifier('exports')),
            stmt.argument
          )
        )
      );
    } else {
      newBodyStmts.push(stmt);
    }
  }

  const replacement = [...(needsInterop ? [interopDecl] : []), ...requireStmts, ...newBodyStmts];

  const defineStatement = j(defineCall).closest(j.ExpressionStatement);
  defineStatement.replaceWith(() => replacement);

  return root.toSource({quote: 'single'});
};

module.exports.parser = 'babel';

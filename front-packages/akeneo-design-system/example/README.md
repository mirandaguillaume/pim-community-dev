This project consumes your **latest local build** of `akeneo-design-system` — the contents of
`../lib`, not `../src`. That is the whole point of it: the DSM's own unit tests and its Storybook
both run against the sources, so this is the only thing that exercises the build output, where
broken exports, a missing theme or a stale artefact actually show up.

During the install, `../lib` is copied into `./node_modules/akeneo-design-system`.

## Install

```shell script
yarn install
```

## Tests

A few tests checking that the components are correctly exported and render. CI runs exactly this.

```shell script
yarn test
```

If you rebuilt `akeneo-design-system`, pick the new build up with:

```shell script
yarn upgrade akeneo-design-system
```

## Why there is no dev server here

There used to be a `yarn start`, from the Create React App this project was built on. It had been
broken since March 2026 — `src/index.js` was rewritten to React 18's `createRoot` while the
dependency stayed on React 17 — and nothing noticed, because CI only ever ran `yarn test`, which
imports `App` directly and never the browser entry point.

Replacing it with a modern bundler is not currently possible: `../lib/index.js` is a barrel of ten
`__exportStar(require(...), exports)` calls, which copy properties in a `for..in` loop **at
runtime**. webpack, rspack and Jest resolve those names at runtime and work; rollup and esbuild
analyse exports statically and see nothing, so `import {Badge} from 'akeneo-design-system'` fails to
build. Making the DSM's output consumable by an ESM bundler is a change to the DSM's own build, not
to this project.

For an interactive playground in the meantime, use the DSM's Storybook: `yarn storybook:start`.

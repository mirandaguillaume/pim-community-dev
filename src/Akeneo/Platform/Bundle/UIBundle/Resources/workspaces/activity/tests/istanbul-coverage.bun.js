/* global Bun */
'use strict';

const {createInstrumenter} = require('istanbul-lib-instrument');
const path = require('path');

// Workspace src/ directory — resolved relative to this preload file's location
const WORKSPACE_SRC = path.resolve(__dirname, '..', 'src');

const escapedSrc = WORKSPACE_SRC.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
// Match workspace source files only; exclude .test. and .unit. (activity convention) files
const SRC_FILTER = new RegExp(escapedSrc + '/(?!.*\\.(test|unit)\\.[jt]sx?).*\\.[jt]sx?$');

const instrumenter = createInstrumenter({esModules: true, produceSourceMap: false});

Bun.plugin({
  name: 'istanbul-instrument',
  setup(build) {
    build.onLoad({filter: SRC_FILTER}, async ({path: filePath}) => {
      const source = await Bun.file(filePath).text();

      const isTsx = /\.tsx$/.test(filePath);
      const transpiler = new Bun.Transpiler({
        loader: isTsx ? 'tsx' : 'ts',
        trimUnusedImports: true,
        ...(isTsx && {tsconfig: JSON.stringify({compilerOptions: {jsx: 'react', jsxFactory: 'React.createElement'}})}),
      });
      let jsSource;
      try {
        jsSource = transpiler.transformSync(source);
      } catch {
        // Fall back to raw source if transpilation fails
        jsSource = source;
      }

      let instrumented;
      try {
        instrumented = instrumenter.instrumentSync(jsSource, filePath);
      } catch {
        instrumented = jsSource;
      }

      return {contents: instrumented, loader: 'js'};
    });
  },
});

// Write Istanbul lcov after all tests complete
afterAll(() => {
  const coverage = global.__coverage__;
  if (!coverage || Object.keys(coverage).length === 0) return;

  const {createCoverageMap} = require('istanbul-lib-coverage');
  const {createContext} = require('istanbul-lib-report');
  const reports = require('istanbul-reports');

  const coverageMap = createCoverageMap(coverage);
  const coverageDir = path.resolve(__dirname, '..', 'coverage');

  const context = createContext({dir: coverageDir, coverageMap});
  reports.create('lcov').execute(context);
  reports.create('json-summary').execute(context);
  reports.create('text-summary').execute(context);
});

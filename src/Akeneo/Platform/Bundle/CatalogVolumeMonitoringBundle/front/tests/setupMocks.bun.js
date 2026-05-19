/* global Bun */
const {JSDOM} = require('jsdom');
const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>', {url: 'http://localhost'});
const {window: jsdomWindow} = dom;
global.window = jsdomWindow;
global.document = jsdomWindow.document;
global.navigator = jsdomWindow.navigator;
global.HTMLElement = jsdomWindow.HTMLElement;
global.Element = jsdomWindow.Element;
global.Event = jsdomWindow.Event;
global.CustomEvent = jsdomWindow.CustomEvent;
global.MutationObserver = jsdomWindow.MutationObserver;
global.getComputedStyle = jsdomWindow.getComputedStyle.bind(jsdomWindow);
global.sessionStorage = jsdomWindow.sessionStorage;
global.localStorage = jsdomWindow.localStorage;

require('@testing-library/jest-dom');

// Bun --coverage runs test files in a shared context; explicit cleanup prevents DOM leakage.
const {cleanup} = require('@testing-library/react');
afterEach(cleanup);

// Reset mock call counts between tests (Bun coverage shares mock instances).
beforeEach(() => jest.clearAllMocks());

// IntersectionObserver is not in jsdom — stub it (replaces setupTests.ts beforeEach)
global.window.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
}));

Bun.plugin({
  name: 'asset-and-umd-mock',
  setup(build) {
    build.onLoad({filter: /\.(svg|png|jpg|jpeg|gif|ico|css)$/}, () => ({
      contents: 'module.exports = "";',
      loader: 'js',
    }));
    build.onLoad({filter: /akeneo-design-system\/node_modules\//}, () => ({
      contents: 'module.exports = {};',
      loader: 'js',
    }));
  },
});

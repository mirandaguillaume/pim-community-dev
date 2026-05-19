// Bun 1.3.x --dom flag is a no-op; bootstrap jsdom manually (equivalent to Jest's testEnvironment: 'jsdom')
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

// Return empty module for assets and problematic UMD bundles (replaces Jest's moduleNameMapper)
Bun.plugin({
  name: 'asset-and-umd-mock',
  setup(build) {
    build.onLoad({filter: /\.(svg|png|jpg|jpeg|gif|ico|css)$/}, () => ({
      contents: 'module.exports = "";',
      loader: 'js',
    }));
    // WYSIWYG packages inside akeneo-design-system use window in UMD wrappers at eval time
    build.onLoad({filter: /akeneo-design-system\/node_modules\//}, () => ({
      contents: 'module.exports = {};',
      loader: 'js',
    }));
  },
});

// Bun requires explicit factory (no __mocks__ auto-discovery)
jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts', () => ({
  dependencies: {
    router: {
      generate: jest.fn(route => route),
      redirect: jest.fn(url => url),
      redirectToRoute: jest.fn((route, params) => `${route}?${JSON.stringify(params)}`),
    },
    translate: jest.fn(key => key),
    viewBuilder: undefined,
    notify: jest.fn((level, message) => `${level} ${message}`),
    user: {
      get: jest.fn(data => {
        if (data === 'catalogLocale' || data === 'uiLocale') return 'en_US';
        return data;
      }),
      set: jest.fn(),
    },
    security: {isGranted: jest.fn(() => true)},
    mediator: {
      trigger: jest.fn(event => event),
      on: jest.fn(event => event),
      off: jest.fn(event => event),
    },
    featureFlags: {isEnabled: jest.fn(() => false)},
    systemConfiguration: {
      initialize: jest.fn(),
      refresh: jest.fn(),
      get: jest.fn(key => key),
    },
  },
}));

global.fetch = require('jest-fetch-mock');
global.fetchMock = global.fetch;

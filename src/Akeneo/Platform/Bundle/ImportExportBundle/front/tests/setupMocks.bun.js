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

// @testing-library/jest-dom extends expect with toBeInTheDocument, toHaveTextContent, etc.
require('@testing-library/jest-dom');

// Intercept asset and UMD bundle imports that would fail in Bun's ESM context.
Bun.plugin({
  name: 'asset-and-umd-mock',
  setup(build) {
    build.onLoad({filter: /\.(svg|png|jpg|jpeg|gif|ico|css)$/}, () => ({
      contents: 'module.exports = "";',
      loader: 'js',
    }));
    // react-draft-wysiwyg and similar UMD packages inside akeneo-design-system
    // reference window at IIFE evaluation time — short-circuit them.
    build.onLoad({filter: /akeneo-design-system\/node_modules\//}, () => ({
      contents: 'module.exports = {};',
      loader: 'js',
    }));
  },
});

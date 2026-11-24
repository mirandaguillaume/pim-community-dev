jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

// Must run BEFORE jest-fetch-mock is loaded: jest-fetch-mock -> cross-fetch -> node-fetch 2.7 loads
// whatwg-url. Its own nested whatwg-url@5 does not need TextEncoder, but the root's whatwg-url@11
// reads it at load time and jest-environment-jsdom does not provide it. react-router 7 needs it
// as well, so the polyfill stays.
if (typeof global.TextEncoder === 'undefined') {
  const {TextEncoder, TextDecoder} = require('util');
  Object.assign(global, {TextEncoder, TextDecoder});
}

global.fetch = require('jest-fetch-mock');
global.fetchMock = global.fetch;

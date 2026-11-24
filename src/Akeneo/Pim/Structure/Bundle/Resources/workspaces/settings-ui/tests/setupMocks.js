jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

// Must run BEFORE jest-fetch-mock is loaded: jest-fetch-mock -> cross-fetch -> node-fetch 2.7 loads
// whatwg-url, which reads TextEncoder at load time and jest-environment-jsdom does not provide it.
// Under this config's moduleDirectories Jest even resolves node-fetch's own whatwg-url@5 to the
// root's whatwg-url@11, which is the version that needs it. react-router 7 needs it as well.
if (typeof global.TextEncoder === 'undefined') {
  const {TextEncoder, TextDecoder} = require('util');
  Object.assign(global, {TextEncoder, TextDecoder});
}

global.fetch = require('jest-fetch-mock');
global.fetchMock = global.fetch;

jest.mock(
  'pim/fetcher-registry',
  () => ({
    getFetcher: jest.fn(() => ({
      clear: jest.fn(),
      fetchAll: jest.fn(() => Promise.resolve({})),
      fetch: jest.fn(() => Promise.resolve({})),
      getAll: jest.fn(() => Promise.resolve([])),
    })),
  }),
  {virtual: true}
);

jest.mock(
  'pim/user-context',
  () => ({
    get: jest.fn(key => {
      if (key === 'catalogLocale' || key === 'uiLocale') return 'en_US';
      return key;
    }),
    set: jest.fn(),
  }),
  {virtual: true}
);

jest.mock(
  'pim/datagrid/state',
  () => ({
    get: jest.fn(key => key),
    set: jest.fn(),
  }),
  {virtual: true}
);

jest.mock('oro/translator', () => jest.fn(key => key), {virtual: true});

jest.mock(
  'routing',
  () => ({
    generate: jest.fn(route => route),
  }),
  {virtual: true}
);

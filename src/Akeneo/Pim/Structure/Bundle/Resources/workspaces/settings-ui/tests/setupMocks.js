jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

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

import '@testing-library/jest-dom/extend-expect';

declare global {
  // Allow NodeJS.Global to carry fetch for test mocks
  // eslint-disable-next-line no-namespace
  namespace NodeJS {
    interface Global {
      fetch: typeof fetch;
    }
  }
}

beforeEach(() => {
  // Default no-op mock; individual tests can override as needed
  global.fetch = jest.fn() as unknown as typeof fetch;
});

export {};

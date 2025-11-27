import '@testing-library/jest-dom/extend-expect';

beforeEach(() => {
  // Default no-op mock; individual tests can override as needed
  (globalThis as unknown as {fetch: typeof fetch}).fetch = jest.fn() as unknown as typeof fetch;
});

export {};

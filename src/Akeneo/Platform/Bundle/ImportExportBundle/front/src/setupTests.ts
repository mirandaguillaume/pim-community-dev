import '@testing-library/jest-dom/extend-expect';

declare global {
  // Allow mocking fetch in tests
  // eslint-disable-next-line no-var
  var fetch: typeof fetch;
}

beforeEach(() => {
  // Default no-op mock; individual tests can override as needed
  global.fetch = jest.fn() as unknown as typeof fetch;
});

export {};

// Provide a global fetch stub so that jest.spyOn(global, 'fetch') works in
// jest-environment-jsdom, which strips Node 18+ built-in fetch.
// Returns a safe no-op Response so that unmocked fetch() calls in component
// effects (e.g. useJobExecutionTypes) do not crash the Jest worker with
// unhandled rejections.
if (typeof global.fetch === 'undefined') {
  global.fetch = () =>
    Promise.resolve({
      ok: true,
      status: 200,
      json: () => Promise.resolve([]),
      text: () => Promise.resolve(''),
    });
}

// Provide a global fetch stub so that jest.spyOn(global, 'fetch') works in
// jest-environment-jsdom, which strips Node 18+ built-in fetch.
if (typeof global.fetch === 'undefined') {
  global.fetch = () => Promise.reject(new Error('fetch is not mocked'));
}

// Provide a global fetch stub so that jest.spyOn(global, 'fetch') works in
// jest-environment-jsdom, which strips Node 18+ built-in fetch.
if (typeof global.fetch === 'undefined') {
  global.fetch = () => Promise.reject(new Error('fetch is not mocked'));
}

// react-router 7 reads TextEncoder as soon as it is loaded, and jest-environment-jsdom does not
// provide it: without this, every suite importing react-router fails with "TextEncoder is not defined".
if (typeof global.TextEncoder === 'undefined') {
  const {TextEncoder, TextDecoder} = require('util');
  Object.assign(global, {TextEncoder, TextDecoder});
}

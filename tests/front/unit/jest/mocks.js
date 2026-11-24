jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

// react-router 7 reads TextEncoder as soon as it is loaded, and jest-environment-jsdom does not
// provide it: without this, every suite importing react-router fails with "TextEncoder is not defined".
if (typeof global.TextEncoder === 'undefined') {
  const {TextEncoder, TextDecoder} = require('util');
  Object.assign(global, {TextEncoder, TextDecoder});
}

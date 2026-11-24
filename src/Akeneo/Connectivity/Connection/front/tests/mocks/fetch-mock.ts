import type {GlobalWithFetchMock} from 'jest-fetch-mock';
import {TextDecoder, TextEncoder} from 'util';

// Must run BEFORE jest-fetch-mock is loaded: jest-fetch-mock -> cross-fetch -> node-fetch 2.7 loads
// whatwg-url. Its own nested whatwg-url@5 does not need TextEncoder, but the root's whatwg-url@11
// reads it at load time and jest-environment-jsdom does not provide it. react-router 7 needs it
// as well, so the polyfill stays.
if (typeof global.TextEncoder === 'undefined') {
    Object.assign(global, {TextEncoder, TextDecoder});
}

const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

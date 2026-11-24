import type {GlobalWithFetchMock} from 'jest-fetch-mock';
import {TextDecoder, TextEncoder} from 'util';

// Must run BEFORE jest-fetch-mock is loaded: jest-fetch-mock -> cross-fetch -> node-fetch 2.7 loads
// whatwg-url, which reads TextEncoder at load time and jest-environment-jsdom does not provide it.
// Under this config's moduleDirectories Jest even resolves node-fetch's own whatwg-url@5 to the
// root's whatwg-url@11, which is the version that needs it. react-router 7 needs it as well.
if (typeof global.TextEncoder === 'undefined') {
    Object.assign(global, {TextEncoder, TextDecoder});
}

const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

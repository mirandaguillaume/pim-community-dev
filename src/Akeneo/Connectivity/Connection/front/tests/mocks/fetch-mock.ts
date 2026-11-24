import {GlobalWithFetchMock} from 'jest-fetch-mock';
import {TextDecoder, TextEncoder} from 'util';

const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

// react-router 7 reads TextEncoder as soon as it is loaded, and jest-environment-jsdom does not
// provide it: without this, every suite importing react-router fails with "TextEncoder is not defined".
if (typeof global.TextEncoder === 'undefined') {
    Object.assign(global, {TextEncoder, TextDecoder});
}

import {GlobalWithFetchMock} from 'jest-fetch-mock';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

// Prevent dangling fetch mocks from crashing Node with unhandled rejections.
// Some tests trigger async fetches (e.g. React Query) that resolve after the
// test completes. When jest-fetch-mock returns an empty body and the code calls
// .json(), node-fetch throws a FetchError that goes unhandled.
process.on('unhandledRejection', (reason: unknown) => {
  if (reason instanceof Error && reason.name === 'FetchError') {
    return;
  }
  console.error('Unhandled promise rejection:', reason);
  process.exit(1);
});

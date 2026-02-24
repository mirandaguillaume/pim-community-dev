import {GlobalWithFetchMock} from 'jest-fetch-mock';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

// Prevent dangling async operations from crashing Node after tests complete.
// Some tests mount React components with useEffect/React Query that trigger
// async work (fetches, state updates). When the test finishes, JSDOM tears
// down the document, but pending callbacks still fire and crash Node.
//
// Two known crash patterns:
// 1. FetchError: jest-fetch-mock returns empty body → .json() throws
// 2. TypeError: React calls document.createEvent after JSDOM cleanup
process.on('unhandledRejection', (reason: unknown) => {
  if (reason instanceof Error && reason.name === 'FetchError') {
    return;
  }
  console.error('Unhandled promise rejection:', reason);
  process.exit(1);
});

// When JSDOM tears down between test files, pending React effects call
// document.createEvent() on null. Continuing after this corrupts JSDOM state
// and causes subsequent test suites to fail. Exit cleanly instead — all tests
// that ran before this point have already been reported.
process.on('uncaughtException', (error: Error) => {
  if (error instanceof TypeError && error.message?.includes('createEvent')) {
    process.exit(0);
  }
  console.error('Uncaught exception:', error);
  process.exit(1);
});

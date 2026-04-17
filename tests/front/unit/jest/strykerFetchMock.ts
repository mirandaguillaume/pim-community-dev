/**
 * Stryker-safe fetch mock setup.
 *
 * Identical to fetchMock.ts but replaces process.exit() calls with silent
 * suppression. In the Stryker sandbox, process.exit() kills the child worker
 * and Stryker reports "Something went wrong in the initial test run".
 *
 * Known JSDOM teardown errors (createEvent TypeError, FetchError on empty body)
 * are suppressed silently — they are benign cleanup race conditions, not real
 * test failures.
 */
import {GlobalWithFetchMock} from 'jest-fetch-mock';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

process.on('unhandledRejection', (reason: unknown) => {
  if (reason instanceof Error && reason.name === 'FetchError') {
    return;
  }
  // Log but do NOT exit — Stryker manages the worker lifecycle.
  console.error('Unhandled promise rejection:', reason);
});

process.on('uncaughtException', (error: Error) => {
  if (error instanceof TypeError && error.message?.includes('createEvent')) {
    return; // Suppress JSDOM teardown noise — do NOT exit.
  }
  // Log but do NOT exit — Stryker manages the worker lifecycle.
  console.error('Uncaught exception:', error);
});

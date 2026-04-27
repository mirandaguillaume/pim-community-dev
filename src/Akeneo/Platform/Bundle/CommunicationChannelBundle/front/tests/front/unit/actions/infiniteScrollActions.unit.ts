import {
  INFINITE_SCROLL_FETCHING_RESULTS,
  INFINITE_SCROLL_FIRST_RESULTS_FETCHED,
  INFINITE_SCROLL_NEXT_RESULTS_FETCHED,
  INFINITE_SCROLL_RESULTS_NOT_FETCHED,
  infiniteScrollFetchingResults,
  infiniteScrollFirstResultsFetched,
  infiniteScrollNextResultsFetched,
  infiniteScrollResultsNotFetched,
} from '../../../../src/actions/infiniteScrollActions';

test('infiniteScrollFetchingResults returns the correct action type', () => {
  const action = infiniteScrollFetchingResults();
  expect(action.type).toBe(INFINITE_SCROLL_FETCHING_RESULTS);
});

test('infiniteScrollFirstResultsFetched returns items in the payload', () => {
  const items = [{id: 1}, {id: 2}];
  const action = infiniteScrollFirstResultsFetched(items);
  expect(action.type).toBe(INFINITE_SCROLL_FIRST_RESULTS_FETCHED);
  expect(action.payload.items).toBe(items);
});

test('infiniteScrollNextResultsFetched returns items and lastAppend in the payload', () => {
  const items = [{id: 3}];
  const action = infiniteScrollNextResultsFetched(items, true);
  expect(action.type).toBe(INFINITE_SCROLL_NEXT_RESULTS_FETCHED);
  expect(action.payload.items).toBe(items);
  expect(action.payload.lastAppend).toBe(true);
});

test('infiniteScrollNextResultsFetched passes lastAppend=false correctly', () => {
  const action = infiniteScrollNextResultsFetched([], false);
  expect(action.payload.lastAppend).toBe(false);
});

test('infiniteScrollResultsNotFetched returns the correct action type', () => {
  const action = infiniteScrollResultsNotFetched();
  expect(action.type).toBe(INFINITE_SCROLL_RESULTS_NOT_FETCHED);
});

test('action type constants have canonical string values', () => {
  expect(INFINITE_SCROLL_FETCHING_RESULTS).toBe('INFINITE_SCROLL_FETCHING_RESULTS');
  expect(INFINITE_SCROLL_FIRST_RESULTS_FETCHED).toBe('INFINITE_SCROLL_FIRST_RESULTS_FETCHED');
  expect(INFINITE_SCROLL_NEXT_RESULTS_FETCHED).toBe('INFINITE_SCROLL_NEXT_RESULTS_FETCHED');
  expect(INFINITE_SCROLL_RESULTS_NOT_FETCHED).toBe('INFINITE_SCROLL_RESULTS_NOT_FETCHED');
});

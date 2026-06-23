import {configureStore} from '@reduxjs/toolkit';
import gridStateReducer from '../../../Resources/public/js/datagrid/gridStateSlice';
import createGridStateMirror from '../../../Resources/public/js/datagrid/createGridStateMirror';

const makeStore = () =>
  configureStore({
    reducer: {gridState: gridStateReducer},
    middleware: getDefault => getDefault({serializableCheck: false}),
  });

/** A minimal fake of the Backbone PageableCollection: a live `state` object + an event bus. */
const makeCollection = (state: Record<string, unknown>) => {
  const handlers: Record<string, Array<() => void>> = {};

  return {
    state,
    on(event: string, handler: () => void) {
      handlers[event] = handlers[event] || [];
      handlers[event].push(handler);
    },
    off(event: string, handler: () => void) {
      handlers[event] = (handlers[event] || []).filter(h => h !== handler);
    },
    trigger(event: string) {
      (handlers[event] || []).forEach(h => h());
    },
  };
};

const baseState = () => ({currentPage: 1, pageSize: 25, totalRecords: 0, totalPages: 1, sorters: {}, filters: {}});

test('seeds the store with the current collection state on creation', () => {
  const store = makeStore();
  const collection = makeCollection({...baseState(), currentPage: 3, pageSize: 50, totalRecords: 10, filters: {a: 1}});

  createGridStateMirror(collection, store);

  expect(store.getState().gridState.currentPage).toBe(3);
  expect(store.getState().gridState.filters).toEqual({a: 1});
});

test('re-syncs the store on each updateState event', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  createGridStateMirror(collection, store);

  collection.state.currentPage = 4;
  collection.state.totalRecords = 80;
  collection.trigger('updateState');

  expect(store.getState().gridState.currentPage).toBe(4);
  expect(store.getState().gridState.totalRecords).toBe(80);
});

test('the teardown detaches the updateState listener', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  const teardown = createGridStateMirror(collection, store);

  teardown();
  collection.state.currentPage = 9;
  collection.trigger('updateState');

  expect(store.getState().gridState.currentPage).toBe(1);
});

test('does not share the live filters reference with the store (clone, so Immer cannot freeze it)', () => {
  const store = makeStore();
  const liveFilters = {enabled: true};
  const collection = makeCollection({...baseState(), filters: liveFilters});

  createGridStateMirror(collection, store);

  // The store froze its copy; mutating the live object must stay legal (not the frozen one).
  expect(() => {
    (liveFilters as Record<string, unknown>).enabled = false;
  }).not.toThrow();
  expect(store.getState().gridState.filters).toEqual({enabled: true});
});

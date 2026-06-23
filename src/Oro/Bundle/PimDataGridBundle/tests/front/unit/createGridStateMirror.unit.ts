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

test('re-syncs on the `reset` event (the real post-fetch settle signal)', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  createGridStateMirror(collection, store);

  // Simulate a settled fetch: parse() has updated totalRecords/currentPage, then reset() fires `reset`.
  collection.state.currentPage = 2;
  collection.state.totalRecords = 137;
  collection.state.filters = {enabled: true};
  collection.trigger('reset');

  expect(store.getState().gridState.currentPage).toBe(2);
  expect(store.getState().gridState.totalRecords).toBe(137);
  expect(store.getState().gridState.filters).toEqual({enabled: true});
});

test('re-syncs on the `beforeFetch` event (page/filter writes made just before the request)', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  createGridStateMirror(collection, store);

  collection.state.currentPage = 5;
  collection.trigger('beforeFetch');

  expect(store.getState().gridState.currentPage).toBe(5);
});

test('re-syncs on the `updateState` event (the one-shot initial-render signal)', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  createGridStateMirror(collection, store);

  collection.state.currentPage = 4;
  collection.state.totalRecords = 80;
  collection.trigger('updateState');

  expect(store.getState().gridState.currentPage).toBe(4);
  expect(store.getState().gridState.totalRecords).toBe(80);
});

test('the teardown detaches every settle listener (reset/beforeFetch/updateState)', () => {
  const store = makeStore();
  const collection = makeCollection(baseState());
  const teardown = createGridStateMirror(collection, store);

  teardown();
  collection.state.currentPage = 9;
  collection.trigger('reset');
  collection.trigger('beforeFetch');
  collection.trigger('updateState');

  expect(store.getState().gridState.currentPage).toBe(1);
});

test('does not share the live filters reference with the store (clone, so Immer cannot freeze it)', () => {
  const store = makeStore();
  const liveFilters = {enabled: true};
  const collection = makeCollection({...baseState(), filters: liveFilters});

  createGridStateMirror(collection, store);

  expect(() => {
    (liveFilters as Record<string, unknown>).enabled = false;
  }).not.toThrow();
  expect(store.getState().gridState.filters).toEqual({enabled: true});
});

test('does not freeze the live sorters object (Backbone mutates it IN PLACE via setSorting)', () => {
  const store = makeStore();
  const liveSorters = {label: 1};
  const collection = makeCollection({...baseState(), sorters: liveSorters});

  createGridStateMirror(collection, store); // dispatch → Immer freezes the store's clone

  // setSorting does `delete state.sorters[k]` / `state.sorters[k] = order` in place — must stay legal.
  expect(() => {
    delete (liveSorters as Record<string, unknown>).label;
    (liveSorters as Record<string, unknown>).price = -1;
  }).not.toThrow();
  expect(store.getState().gridState.sorters).toEqual({label: 1});
});

test('does not freeze the live parameters object (setAdditionalParameter mutates it IN PLACE)', () => {
  const store = makeStore();
  const liveParameters = {scope: 'ecommerce'};
  const collection = makeCollection({...baseState(), parameters: liveParameters});

  createGridStateMirror(collection, store);

  // setAdditionalParameter does `state.parameters[name] = value` / `delete state.parameters[name]`.
  expect(() => {
    (liveParameters as Record<string, unknown>).locale = 'en_US';
    delete (liveParameters as Record<string, unknown>).scope;
  }).not.toThrow();
  expect(store.getState().gridState.parameters).toEqual({scope: 'ecommerce'});
});

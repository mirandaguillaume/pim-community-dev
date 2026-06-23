import {GridStore} from './createGridStore';
import {setGridState, toGridState} from './gridStateSlice';

/**
 * The minimal slice of a Backbone `PageableCollection` the mirror needs: the live `state` object and
 * the event bus.
 */
type StatefulCollection = {
  state: Record<string, unknown>;
  on(event: string, handler: () => void): void;
  off(event: string, handler: () => void): void;
};

/**
 * The collection events the mirror re-syncs on — the SAME set `collection-filters-manager.js` binds
 * to track state. `reset` (Backbone-native, fired by `PageableCollection.reset` after each fetch, once
 * `parse` has set totalRecords/totalPages) is the settled signal; `beforeFetch` captures the page/sort/
 * filter writes made just before a request. `updateState` is the one-shot initial-render signal
 * (`state-listener.js` fires it via `mediator.once('datagrid_filters:rendered')`).
 *
 * NB: `reset` is required — the collection's `updateState()` METHOD (which would `trigger('updateState')`
 * after each state change) has no callers in the bundle, so binding `updateState` alone leaves the
 * mirror stuck on its seed value after the first fetch.
 */
const SYNC_EVENTS = ['reset', 'beforeFetch', 'updateState'];

/**
 * Wave 5 — the bridge that keeps the RTK `gridState` mirror in sync with the authoritative Backbone
 * `collection.state`.
 *
 * Seeds the store with the current state, then re-syncs on each settle event (see `SYNC_EVENTS`). It
 * never touches Backbone (no fetch-path change), so it carries no regression risk: Backbone stays
 * authoritative, RTK is a downstream reflection.
 *
 * Returns a teardown that detaches every listener.
 */
export const createGridStateMirror = (collection: StatefulCollection, store: GridStore): (() => void) => {
  const sync = (): void => {
    store.dispatch(setGridState(toGridState(collection.state)));
  };

  sync();
  SYNC_EVENTS.forEach(event => collection.on(event, sync));

  return () => SYNC_EVENTS.forEach(event => collection.off(event, sync));
};

export default createGridStateMirror;

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
 * Wave 5 — the bridge that keeps the RTK `gridState` mirror in sync with the authoritative Backbone
 * `collection.state`.
 *
 * The collection already fires `this.trigger('updateState', this, this.state)` (pageable-collection.js,
 * in `reset`) after every fetch — i.e. once the state is settled (`totalRecords` received, `totalPages`
 * derived, `currentPage` clamped). The mirror seeds the store with the current state, then re-syncs on
 * each `updateState`. It never touches Backbone (no fetch path change), so it carries no regression
 * risk: Backbone stays authoritative, RTK is a downstream reflection.
 *
 * Returns a teardown that detaches the listener.
 */
export const createGridStateMirror = (collection: StatefulCollection, store: GridStore): (() => void) => {
  const sync = (): void => {
    store.dispatch(setGridState(toGridState(collection.state)));
  };

  sync();
  collection.on('updateState', sync);

  return () => collection.off('updateState', sync);
};

export default createGridStateMirror;

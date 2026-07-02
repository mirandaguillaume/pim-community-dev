import React from 'react';
import {useSelector} from 'react-redux';
import {NoDataBlock} from './NoDataBlock';
import {selectGridStateReadView} from './gridStateSlice';

type Props = {
  /** Empty-state message shown when NO filter is active — "no products exist yet". */
  noEntitiesHintKey: string;
  /** Empty-state message shown when a filter IS active — "nothing matches your filters". */
  noResultsHintKey: string;
  hintParams?: Record<string, string>;
  subHintKey: string;
  imageClass?: string;
};

/**
 * Mirror-consuming container for the grid empty state (C1 Wave 5).
 *
 * This is the FIRST component that reads grid state from the per-grid RTK mirror
 * (`grid.gridStore`) instead of Backbone's `collection.state`. It subscribes to
 * `selectGridStateReadView` and picks the empty-state message reactively: when any
 * filter is active it shows `noResultsHintKey` ("nothing matches your filters"),
 * otherwise `noEntitiesHintKey` ("no products exist yet").
 *
 * The presentational `NoDataBlock` stays store-free so it remains unit-testable
 * without redux; this thin wrapper isolates the react-redux coupling. It requires
 * an ancestor `<Provider store={grid.gridStore}>` (supplied by `grid.js`).
 */
const ConnectedNoDataBlock = ({noEntitiesHintKey, noResultsHintKey, hintParams, subHintKey, imageClass}: Props) => {
  const {filters} = useSelector(selectGridStateReadView);
  const hintKey = Object.keys(filters).length > 0 ? noResultsHintKey : noEntitiesHintKey;

  return <NoDataBlock hintKey={hintKey} hintParams={hintParams} subHintKey={subHintKey} imageClass={imageClass} />;
};

export {ConnectedNoDataBlock};

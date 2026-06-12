import React from 'react';

type View = {
  text: string;
};

type Props = {
  view: View;
  dirtyFilters: boolean;
  dirtyColumns: boolean;
};

/**
 * Presentational label of the currently-selected product-grid view (Select2 selection).
 *
 * Reproduces, byte-for-byte, the markup of the underscore template
 * `pim/template/grid/view-selector/current` (view-selector-current.html): the
 * `.select2-selection-label-view` wrapper, the `*` dirty marker + view label in `.current`,
 * and the (CE-empty) `before`/`after` extension drop-zone spans, kept for byte-identical markup.
 *
 * The dirty flags are computed by the Backbone shell `view-selector-current.js`
 * (`onDatagridStateChange`, fed by the forwarded-events bridge) and passed in as props;
 * this component is purely presentational.
 */
const ViewSelectorCurrent = ({view, dirtyFilters, dirtyColumns}: Props) => (
  <span className="select2-selection-label-view">
    <span className="before" data-drop-zone="before" />
    <span className="current">
      {dirtyColumns || dirtyFilters ? '*' : ''} {view.text}
    </span>
    <span className="after" data-drop-zone="after" />
  </span>
);

export default ViewSelectorCurrent;

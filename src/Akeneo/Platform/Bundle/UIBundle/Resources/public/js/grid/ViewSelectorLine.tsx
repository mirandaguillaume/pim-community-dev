import React from 'react';

type View = {
  id: number;
  text: string;
  type?: string;
};

type Props = {
  view: View;
  isCurrent: boolean;
  publicLabel: string;
};

/**
 * Presentational row of the product-grid view-selector dropdown (one saved view).
 *
 * Reproduces, byte-for-byte, the markup of the underscore template
 * `pim/template/grid/view-selector/line` (view-selector-line.html) so the
 * `.select2-result-label-view`/`.view-line`/`.view-label`/`.view-label-current`/`.view-type`
 * selectors stay a stable Behat contract (Select2Decorator / GridCapableDecorator).
 *
 * Rendered by the Backbone shell `view-selector-line.js` into a Select2-owned container
 * (Select2 `formatResult` appends `form.render().$el`); the shell still owns the data.
 */
const ViewSelectorLine = ({view, isCurrent, publicLabel}: Props) => (
  <div className="select2-result-label-view">
    <div className="view-line">
      <span className={`view-label ${isCurrent ? 'view-label-current' : ''}`}>{view.text}</span>
      {view.type === 'public' && <span className="view-type">{publicLabel}</span>}
    </div>
  </div>
);

export default ViewSelectorLine;

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
  /** Whether the current view has unsaved filter/column changes — shows the `*` marker (C1 Slice C).
   *  Kept OUT of `.view-label` so `getCurrentValue` reads a clean label. */
  dirty?: boolean;
};

/**
 * Presentational row of the product-grid view-selector dropdown (one saved view).
 *
 * Reproduces the markup of the underscore template `pim/template/grid/view-selector/line`
 * (view-selector-line.html) so the `.select2-result-label-view`/`.view-line`/`.view-label`/
 * `.view-label-current`/`.view-type` selectors stay a stable Behat contract (Select2Decorator /
 * GridCapableDecorator).
 *
 * Rendered by `ViewSelectorCombobox` as each DSM `SelectInput.Option`, and reused as the selected
 * value (DSM `currentValueElement`). When it is the current view, the `dirty` `*` marker — formerly
 * carried by the separate `ViewSelectorCurrent` (Select2's `formatSelection`) — is rendered here as a
 * sibling of `.view-label`.
 */
const ViewSelectorLine = ({view, isCurrent, publicLabel, dirty = false}: Props) => (
  <div className="select2-result-label-view">
    <div className="view-line">
      {dirty && <span className="view-dirty">*</span>}
      <span className={`view-label ${isCurrent ? 'view-label-current' : ''}`}>{view.text}</span>
      {view.type === 'public' && <span className="view-type">{publicLabel}</span>}
    </div>
  </div>
);

export default ViewSelectorLine;

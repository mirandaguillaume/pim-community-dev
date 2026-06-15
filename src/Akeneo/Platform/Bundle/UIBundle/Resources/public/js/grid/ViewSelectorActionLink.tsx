import React from 'react';

type Props = {
  action: 'create' | 'save' | 'remove';
  label: string;
  hidden?: boolean;
};

/**
 * Presentational dropdown-menu link for the three product-grid view-selector CRUD actions
 * (Create / Save / Remove).
 *
 * Reproduces, byte-for-byte, the markup of the underscore templates
 * `pim/template/grid/view-selector/{create,save,remove}-view` so the
 * `.create`/`.save`/`.remove` anchors (under their `.create-button`/`.save-button`/`.remove-button`
 * Backbone-shell containers) stay a stable Behat contract (GridCapableDecorator). The Backbone
 * shells keep all behaviour (clicks, modal/dialog, persistence); this component only renders.
 */
const ViewSelectorActionLink = ({action, label, hidden = false}: Props) => (
  <a className={`AknDropdown-menuLink ${action}${hidden ? ' AknDropdown-menuLink--hidden' : ''}`}>{label}</a>
);

export default ViewSelectorActionLink;

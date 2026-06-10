import React from 'react';
import __ from 'oro/translator';
import ReactCellBase from './react-cell-base';
import {EnabledBadge} from './EnabledBadge';

/**
 * Product-grid "enabled" status cell, migrated to React (C1 wave 2).
 *
 * Keeps the `oro/datagrid/enabled-cell` module contract (requirejs.yml) and the
 * `frontend_type: enabled` YAML column config unchanged; only the rendering moves
 * from a Backgrid template to a React badge via {@link ReactCellBase}.
 */
class EnabledCell extends ReactCellBase {
  reactContent(): React.ReactElement | null {
    // PIM-6493: for product models the status is computed from the model subtree,
    // so the cell is intentionally left blank.
    if ('product_model' === this.model.get('document_type')) {
      return null;
    }

    const enabled = true === this.formatter.fromRaw(this.model.get(this.column.get('name')));
    const label = __(
      enabled ? 'pim_enrich.entity.product.module.status.enabled' : 'pim_enrich.entity.product.module.status.disabled'
    );

    return <EnabledBadge enabled={enabled} label={label} />;
  }
}

export = EnabledCell;

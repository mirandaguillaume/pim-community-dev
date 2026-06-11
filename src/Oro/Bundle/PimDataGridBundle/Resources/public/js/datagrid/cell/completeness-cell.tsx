import React from 'react';
import __ from 'oro/translator';
import ReactCellBase from './react-cell-base';
import {CompletenessBadge, type CompletenessLevel} from './CompletenessBadge';

/**
 * Product-grid completeness cell, migrated to React (C1 wave 2).
 * Keeps the `oro/datagrid/completeness-cell` module contract and the legacy
 * rendering: product models show "not available", a missing ratio shows a dash,
 * and a ratio renders a colour-coded AknBadge (100 = success, 0 = important,
 * else warning).
 */
class CompletenessCell extends ReactCellBase {
  reactContent(): React.ReactElement | null {
    if ('product_model' === this.model.get('document_type')) {
      return <>{__('pim_common.not_available')}</>;
    }

    const ratio = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    if (null === ratio || '' === ratio) {
      return <>-</>;
    }

    const level: CompletenessLevel = 100 === ratio ? 'success' : 0 === ratio ? 'important' : 'warning';

    return <CompletenessBadge level={level} label={`${ratio}%`} />;
  }
}

export = CompletenessCell;

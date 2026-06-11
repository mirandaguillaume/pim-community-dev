import React from 'react';
import __ from 'oro/translator';
import ReactCellBase from './react-cell-base';
import {CompletenessBadge, type CompletenessLevel} from './CompletenessBadge';

/**
 * Product-grid complete-variant-product cell, migrated to React (C1 wave 2).
 * Mirror of the completeness cell but for product models: non-models show "not
 * available", missing data shows a dash, and {complete, total} renders a
 * colour-coded AknBadge (ratio 1 = success, 0 or total 0 = important, else warning).
 */
class CompleteVariantProductCell extends ReactCellBase {
  reactContent(): React.ReactElement | null {
    if ('product_model' !== this.model.get('document_type')) {
      return <>{__('pim_common.not_available')}</>;
    }

    const data = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    if (null === data || '' === data) {
      return <>-</>;
    }

    const ratio = data.complete / data.total;
    const level: CompletenessLevel =
      1 === ratio ? 'success' : 0 === ratio || 0 === data.total ? 'important' : 'warning';

    return <CompletenessBadge level={level} label={`${data.complete} / ${data.total}`} />;
  }
}

export = CompleteVariantProductCell;

import React from 'react';
import ReactCellBase from './react-cell-base';

/**
 * Product-grid label cell, migrated to React (C1 wave 2).
 *
 * Keeps the `oro/datagrid/product-and-product-model-label-cell` module contract
 * (requirejs.yml) and the `frontend_type: product-and-product-model-label` YAML
 * column config unchanged; only the rendering moves from a Backgrid template to
 * React via {@link ReactCellBase}.
 *
 * Product models render with an alternate highlight colour (CSS only, via the
 * `className()` hook that Backgrid calls on the `<td>` element).
 */
class ProductAndProductModelLabelCell extends ReactCellBase {
  className(): string {
    let base = 'AknGrid-bodyCell AknGrid-bodyCell--noWrap AknGrid-bodyCell--highlight';
    if ('product_model' === this.model.get('document_type')) {
      base += ' AknGrid-bodyCell--highlightAlternative';
    }
    return base;
  }

  reactContent(): React.ReactElement | null {
    return <>{this.formatter.fromRaw(this.model.get(this.column.get('name'))) ?? ''}</>;
  }

  render() {
    super.render();
    // Mirror the legacy behaviour: the <td> title carries the raw column value
    // for browser-native tooltip on truncated text.
    this.el.title = this.model.get(this.column.get('name')) ?? '';
    return this;
  }
}

export = ProductAndProductModelLabelCell;

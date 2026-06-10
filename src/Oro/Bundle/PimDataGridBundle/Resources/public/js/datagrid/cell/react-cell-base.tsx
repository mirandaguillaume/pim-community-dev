import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import StringCell from 'oro/datagrid/string-cell';

/**
 * Base for read-only product-grid cells rendered with React (C1 wave 2).
 *
 * It fixes the two lifecycle defects of the legacy generic `reactCell`
 * (reactCell.tsx — dead code for the product grid):
 * - it UNMOUNTS the React tree when Backgrid disposes the cell (`remove()`),
 *   preventing a detached-tree leak on every collection refresh/sort/paginate;
 * - it owns the provider wrapping in one place, so subclasses stay thin and only
 *   return their content.
 *
 * Subclasses override `reactContent()` to derive props from `this.model` and
 * return the cell's React element — or `null` to render an empty cell. The
 * `oro/datagrid/{{type}}-cell` module contract and the YAML `frontend_type` are
 * kept intact (Strangler Fig), so the Backbone/Backgrid grid keeps resolving and
 * rendering cells exactly as before.
 */
class ReactCellBase extends StringCell {
  // Members provided by Backgrid.Cell / StringCell at runtime. `declare` keeps them
  // type-only (no emitted initializer that would clobber the framework-set values).
  declare el: HTMLElement;
  declare model: any;
  declare column: any;
  declare formatter: any;

  /**
   * Subclasses override this. Returning `null` renders an empty cell.
   */
  reactContent(): React.ReactElement | null {
    return null;
  }

  render() {
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>{this.reactContent()}</DependenciesProvider>
      </ThemeProvider>,
      this.el
    );

    return this;
  }

  remove() {
    // Unmount BEFORE super.remove() detaches the node: Backgrid disposes cells on
    // every collection refresh, and unmounting first avoids both the React tree
    // leak and React 18's "unmount from a detached root" warning.
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = ReactCellBase;

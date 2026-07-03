import BaseGridTitle from 'pim/common/grid-title';
import mediator from 'oro/mediator';
import ConnectedProductGridTitle from 'oro/datagrid/connected-product-grid-title';

/**
 * Backbone host for the product grid title (C1 Wave 5).
 *
 * The counts are no longer copied off `collection.state` here — they are read reactively
 * from the per-grid RTK mirror by `ConnectedProductGridTitle` (useSelector). This host only
 * mounts that React tree, bound to the grid's store, on each load completion.
 */
class ProductGridTitle extends BaseGridTitle {
  /**
   * Bind only to `grid_load:complete`. The base view also re-rendered on `grid_load:start`,
   * but at that point no counts are loaded (the base rendered nothing — the loading state),
   * and the mirror still holds its initial values, so mounting then would flash a wrong count.
   */
  initialize(config: any): void {
    this.config = config.config;

    mediator.on('grid_load:complete', this.setupCount.bind(this));
  }

  /**
   * Mount the mirror-backed React title bound to this grid's store.
   */
  setupCount(collection: any): any {
    this.renderReact(ConnectedProductGridTitle, {store: collection.gridStore, config: this.config}, this.el);

    return this;
  }
}

export = ProductGridTitle;

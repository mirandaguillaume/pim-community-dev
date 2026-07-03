import React from 'react';
import {Provider, useSelector} from 'react-redux';
import __ from 'oro/translator';
import {selectGridState} from './gridStateSlice';
import {GridStore} from './createGridStore';

type TitleConfig = {title: string};

type ViewProps = {config: TitleConfig};

/**
 * Product grid title, read reactively from the RTK mirror (C1 Wave 5).
 *
 * Reproduces `ProductGridTitle`'s three-way count text: products only, product-models
 * only, or both (`"<n> products, <m> product models"`). The counts come from the full
 * `selectGridState` (the read-view omits `totalProducts`/`totalProductModels`).
 *
 * The translations are plain text, so returning the string inside a Fragment renders a
 * single text node into the host element — byte-identical to the old `this.$el.html(text)`
 * and preserving the Behat title contract.
 */
const ProductGridTitleView = ({config}: ViewProps) => {
  const {totalRecords, totalProducts = null, totalProductModels = null} = useSelector(selectGridState);

  let title: string;

  if (totalProducts || totalProductModels) {
    const productCount = __(
      'pim_enrich.entity.product.page_title.product',
      {count: totalProducts},
      totalProducts as number
    );
    const productModelCount = __(
      'pim_enrich.entity.product.page_title.product_model',
      {count: totalProductModels},
      totalProductModels as number
    );

    if (totalProducts && !totalProductModels) {
      title = productCount;
    } else if (!totalProducts && totalProductModels) {
      title = productModelCount;
    } else {
      title = __('pim_enrich.entity.product.page_title.product_and_product_model', {productCount, productModelCount});
    }
  } else {
    title = __(config.title, {count: totalRecords}, totalRecords);
  }

  return <>{title}</>;
};

type Props = ViewProps & {store: GridStore};

/**
 * Mirror-consuming host wrapper (C1 Wave 5). Self-contained — wraps its own
 * `<Provider store={grid.gridStore}>` so the Backbone host mounts it through the
 * standard `renderReact` bridge. Third mirror consumer after ConnectedNoDataBlock (#316)
 * and ConnectedPaginationBar (#318).
 */
const ConnectedProductGridTitle = ({store, config}: Props) => (
  <Provider store={store}>
    <ProductGridTitleView config={config} />
  </Provider>
);

export default ConnectedProductGridTitle;

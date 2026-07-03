jest.mock(
  'oro/translator',
  () =>
    (key: string, params: Record<string, unknown> = {}, count?: number) => {
      const p = Object.entries(params)
        .map(([k, v]) => `${k}=${v}`)
        .join(',');

      return count !== undefined ? `${key}[${p}|${count}]` : `${key}[${p}]`;
    },
  {virtual: true}
);

import React from 'react';
import {act, render} from '@testing-library/react';
import ConnectedProductGridTitle from '../../../Resources/public/js/datagrid/ConnectedProductGridTitle';
import {createGridStore} from '../../../Resources/public/js/datagrid/createGridStore';
import {setGridState, toGridState} from '../../../Resources/public/js/datagrid/gridStateSlice';

const config = {title: 'pim_enrich.entity.product.page_title.index'};

const storeWith = (counts: {totalRecords: number; totalProducts?: number; totalProductModels?: number}) => {
  const store = createGridStore();
  store.dispatch(setGridState(toGridState(counts)));

  return store;
};

const renderTitle = (store: ReturnType<typeof createGridStore>) =>
  render(<ConnectedProductGridTitle store={store} config={config} />);

describe('ConnectedProductGridTitle', () => {
  test('shows the product-only count when there are no product models', () => {
    const {container} = renderTitle(storeWith({totalRecords: 5, totalProducts: 5, totalProductModels: 0}));

    expect(container.textContent).toBe('pim_enrich.entity.product.page_title.product[count=5|5]');
  });

  test('shows the product-model-only count when there are no products', () => {
    const {container} = renderTitle(storeWith({totalRecords: 3, totalProducts: 0, totalProductModels: 3}));

    expect(container.textContent).toBe('pim_enrich.entity.product.page_title.product_model[count=3|3]');
  });

  test('combines both counts when products and product models are present', () => {
    const {container} = renderTitle(storeWith({totalRecords: 7, totalProducts: 5, totalProductModels: 2}));

    // The combined key wraps both sub-counts.
    expect(container.textContent).toContain('pim_enrich.entity.product.page_title.product_and_product_model');
    expect(container.textContent).toContain('pim_enrich.entity.product.page_title.product[count=5|5]');
    expect(container.textContent).toContain('pim_enrich.entity.product.page_title.product_model[count=2|2]');
  });

  test('falls back to the configured title with totalRecords when no product counts are set', () => {
    const {container} = renderTitle(storeWith({totalRecords: 42}));

    expect(container.textContent).toBe('pim_enrich.entity.product.page_title.index[count=42|42]');
  });

  test('reactively updates when the mirror counts change', () => {
    const store = storeWith({totalRecords: 5, totalProducts: 5, totalProductModels: 0});
    const {container} = renderTitle(store);

    expect(container.textContent).toBe('pim_enrich.entity.product.page_title.product[count=5|5]');

    act(() => {
      store.dispatch(setGridState(toGridState({totalRecords: 8, totalProducts: 5, totalProductModels: 3})));
    });

    expect(container.textContent).toContain('pim_enrich.entity.product.page_title.product_and_product_model');
  });
});

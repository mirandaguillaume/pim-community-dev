jest.mock(
  'oro/translator',
  () =>
    (key: string, params: Record<string, string> = {}) => {
      let result = key;
      Object.entries(params).forEach(([k, v]) => {
        result = result.replace(`%${k}%`, v);
      });
      return result;
    },
  {virtual: true}
);

import React from 'react';
import {act, render, screen} from '@testing-library/react';
import {Provider} from 'react-redux';
import {ConnectedNoDataBlock} from '../../../Resources/public/js/datagrid/ConnectedNoDataBlock';
import {createGridStore} from '../../../Resources/public/js/datagrid/createGridStore';
import {setGridState, toGridState} from '../../../Resources/public/js/datagrid/gridStateSlice';

const renderWithFilters = (filters: Record<string, unknown>) => {
  const store = createGridStore();
  store.dispatch(setGridState(toGridState({filters})));

  return render(
    <Provider store={store}>
      <ConnectedNoDataBlock
        noEntitiesHintKey="pim_datagrid.no_entities"
        noResultsHintKey="pim_datagrid.no_results"
        subHintKey="pim_datagrid.no_results_subtitle"
      />
    </Provider>
  );
};

describe('ConnectedNoDataBlock', () => {
  test('shows the "no entities" message when no filter is active', () => {
    renderWithFilters({});

    expect(screen.getByText('pim_datagrid.no_entities')).toBeInTheDocument();
    expect(screen.queryByText('pim_datagrid.no_results')).not.toBeInTheDocument();
  });

  test('shows the "no results" message when a filter is active', () => {
    renderWithFilters({sku: {value: 'abc', type: 1}});

    expect(screen.getByText('pim_datagrid.no_results')).toBeInTheDocument();
    expect(screen.queryByText('pim_datagrid.no_entities')).not.toBeInTheDocument();
  });

  test('reactively flips the message when the mirror is updated', () => {
    const store = createGridStore();
    store.dispatch(setGridState(toGridState({filters: {}})));

    render(
      <Provider store={store}>
        <ConnectedNoDataBlock
          noEntitiesHintKey="pim_datagrid.no_entities"
          noResultsHintKey="pim_datagrid.no_results"
          subHintKey="pim_datagrid.no_results_subtitle"
        />
      </Provider>
    );

    expect(screen.getByText('pim_datagrid.no_entities')).toBeInTheDocument();

    // The dispatch drives a react-redux subscription re-render; wrap it in act() so React 17
    // flushes it before the assertion (otherwise the DOM still shows the pre-update message).
    act(() => {
      store.dispatch(setGridState(toGridState({filters: {sku: {value: 'abc', type: 1}}})));
    });

    expect(screen.getByText('pim_datagrid.no_results')).toBeInTheDocument();
  });

  test('forwards hintParams and subHintKey through to the presentational block', () => {
    const store = createGridStore();
    store.dispatch(setGridState(toGridState({filters: {}})));

    render(
      <Provider store={store}>
        <ConnectedNoDataBlock
          noEntitiesHintKey="no %entityHint% found"
          noResultsHintKey="pim_datagrid.no_results"
          hintParams={{entityHint: 'products'}}
          subHintKey="pim_datagrid.no_results_subtitle"
        />
      </Provider>
    );

    expect(screen.getByText('no products found')).toBeInTheDocument();
    expect(screen.getByText('pim_datagrid.no_results_subtitle')).toBeInTheDocument();
  });
});

import React from 'react';
import {act, render, screen} from '@testing-library/react';
import ConnectedPaginationBar from '../../../Resources/public/js/datagrid/ConnectedPaginationBar';
import {createGridStore} from '../../../Resources/public/js/datagrid/createGridStore';
import {setGridState, toGridState} from '../../../Resources/public/js/datagrid/gridStateSlice';

const config = {firstPage: 1, windowSize: 3, maxRescoreWindow: 10000, mode: 'server', gapLabel: '...'};

const storeWith = (position: {currentPage: number; pageSize: number; totalRecords: number; totalPages: number}) => {
  const store = createGridStore();
  store.dispatch(setGridState(toGridState(position)));

  return store;
};

// 10 pages (250 / 25), current page 5 — the reference multi-page layout.
const tenPages = {currentPage: 5, pageSize: 25, totalRecords: 250, totalPages: 10};

describe('ConnectedPaginationBar', () => {
  test('rebuilds the page window from the mirror: first, window around current, last, with gaps', () => {
    render(<ConnectedPaginationBar store={storeWith(tenPages)} enabled={true} config={config} />);

    expect(screen.getByTitle('No. 1')).toBeInTheDocument();
    expect(screen.getByTitle('No. 4')).toBeInTheDocument();
    expect(screen.getByTitle('No. 6')).toBeInTheDocument();
    expect(screen.getByTitle('No. 10')).toBeInTheDocument();
    // Two `...` fast-forward gaps: between 1 and 4, and between 6 and 10.
    expect(screen.getAllByTitle('...')).toHaveLength(2);
  });

  test('marks the current page active', () => {
    render(<ConnectedPaginationBar store={storeWith(tenPages)} enabled={true} config={config} />);

    expect(screen.getByTitle('No. 5')).toHaveClass('active', 'AknActionButton--highlight');
    expect(screen.getByTitle('No. 1')).not.toHaveClass('active');
  });

  test('renders nothing for a single page', () => {
    const {container} = render(
      <ConnectedPaginationBar
        store={storeWith({currentPage: 1, pageSize: 25, totalRecords: 10, totalPages: 1})}
        enabled={true}
        config={config}
      />
    );

    expect(container.querySelectorAll('a')).toHaveLength(0);
  });

  test('disables every button when the bar is not enabled', () => {
    render(<ConnectedPaginationBar store={storeWith(tenPages)} enabled={false} config={config} />);

    expect(screen.getByTitle('No. 5')).toHaveClass('disabled');
    expect(screen.getByTitle('No. 1')).toHaveClass('disabled');
  });

  test('reactively rebuilds when the mirror position changes', () => {
    const store = storeWith(tenPages);
    render(<ConnectedPaginationBar store={store} enabled={true} config={config} />);

    // Page 5 window shows 4-5-6, so No. 2 is not rendered yet.
    expect(screen.getByTitle('No. 5')).toHaveClass('active');
    expect(screen.queryByTitle('No. 2')).not.toBeInTheDocument();

    act(() => {
      store.dispatch(setGridState(toGridState({currentPage: 2, pageSize: 25, totalRecords: 250, totalPages: 10})));
    });

    // Page 2 window shows 1-2-3, so No. 2 is now active and No. 5 has scrolled out.
    expect(screen.getByTitle('No. 2')).toHaveClass('active', 'AknActionButton--highlight');
    expect(screen.queryByTitle('No. 5')).not.toBeInTheDocument();
  });
});

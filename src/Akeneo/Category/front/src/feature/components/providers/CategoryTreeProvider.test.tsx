import React, {useContext} from 'react';
import {render, screen, waitFor, act} from '@testing-library/react';
import {CategoryTreeProvider, CategoryTreeContext} from './CategoryTreeProvider';
import {CategoryTreeModel} from '../../models';

const rootA: CategoryTreeModel = {
  id: 1,
  code: 'master',
  label: 'Master',
  isRoot: true,
  isLeaf: false,
  children: [
    {id: 2, code: 'clothing', label: 'Clothing', isRoot: false, isLeaf: true},
    {id: 3, code: 'shoes', label: 'Shoes', isRoot: false, isLeaf: true},
  ],
};

const rootB: CategoryTreeModel = {
  id: 10,
  code: 'sales',
  label: 'Sales',
  isRoot: true,
  isLeaf: true,
};

const NodeCounter = () => {
  const {nodes} = useContext(CategoryTreeContext);
  return <div data-testid="count">{nodes.length}</div>;
};

describe('CategoryTreeProvider', () => {
  it('initialises nodes from the root via buildNodesFromCategoryTree', async () => {
    render(
      <CategoryTreeProvider root={rootA}>
        <NodeCounter />
      </CategoryTreeProvider>
    );

    // root + 2 children
    await waitFor(() => expect(screen.getByTestId('count').textContent).toBe('3'));
  });

  it('rebuilds nodes when the root prop changes', async () => {
    const {rerender} = render(
      <CategoryTreeProvider root={rootA}>
        <NodeCounter />
      </CategoryTreeProvider>
    );

    await waitFor(() => expect(screen.getByTestId('count').textContent).toBe('3'));

    rerender(
      <CategoryTreeProvider root={rootB}>
        <NodeCounter />
      </CategoryTreeProvider>
    );

    // rootB has no children — only the root node itself
    await waitFor(() => expect(screen.getByTestId('count').textContent).toBe('1'));
  });

  it('exposes setNodes so consumers can update the tree', async () => {
    const ConsumerWithSetter = () => {
      const {nodes, setNodes} = useContext(CategoryTreeContext);
      return (
        <div>
          <div data-testid="count">{nodes.length}</div>
          <button onClick={() => setNodes([])}>clear</button>
        </div>
      );
    };

    render(
      <CategoryTreeProvider root={rootA}>
        <ConsumerWithSetter />
      </CategoryTreeProvider>
    );

    await waitFor(() => expect(screen.getByTestId('count').textContent).toBe('3'));

    act(() => {
      screen.getByText('clear').click();
    });

    expect(screen.getByTestId('count').textContent).toBe('0');
  });
});

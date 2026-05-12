import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Tree} from './Tree';
import {TreeNode} from '../../../models';

const makeNode = (overrides: Partial<TreeNode<{}>> = {}): TreeNode<{}> => ({
  identifier: 1,
  label: 'Root',
  code: 'root',
  parentId: null,
  childrenIds: [],
  data: {},
  type: 'root',
  childrenStatus: 'idle',
  ...overrides,
});

const renderTree = (props: Partial<React.ComponentProps<typeof Tree<{}>>> = {}) =>
  render(
    <ThemeProvider theme={pimTheme}>
      <Tree value={makeNode()} label="Root Category" {...props} />
    </ThemeProvider>
  );

describe('Tree', () => {
  it('renders a tree element at root level', () => {
    renderTree();
    expect(screen.getByRole('tree')).toBeInTheDocument();
  });

  it('renders the label text', () => {
    renderTree({label: 'My Category'});
    expect(screen.getByText('My Category')).toBeInTheDocument();
  });

  it('calls onClick with the node value when clicked', async () => {
    const onClick = jest.fn();
    renderTree({onClick, label: 'Root Category'});
    await userEvent.click(screen.getByText('Root Category'));
    expect(onClick).toHaveBeenCalledWith(expect.objectContaining({identifier: 1}));
  });

  it('calls open when the arrow button is clicked on a closed non-leaf node', async () => {
    const open = jest.fn();
    renderTree({isLeaf: false, isOpen: false, open});
    const buttons = screen.getAllByRole('button');
    await userEvent.click(buttons[0]);
    expect(open).toHaveBeenCalledTimes(1);
  });

  it('calls close when the arrow button is clicked on an open non-leaf node', async () => {
    const close = jest.fn();
    renderTree({isLeaf: false, isOpen: true, close});
    const buttons = screen.getAllByRole('button');
    await userEvent.click(buttons[0]);
    expect(close).toHaveBeenCalledTimes(1);
  });

  it('renders child subtrees when isOpen is true', () => {
    const childNode = makeNode({identifier: 2, parentId: 1, code: 'child'});
    renderTree({
      isOpen: true,
      isLeaf: false,
      children: <Tree value={childNode} label="Child" _isRoot={false} />,
    });
    expect(screen.getByRole('group')).toBeInTheDocument();
    expect(screen.getByText('Child')).toBeInTheDocument();
  });

  it('does not render the subtree container when isOpen is false', () => {
    const childNode = makeNode({identifier: 2, parentId: 1, code: 'child'});
    renderTree({
      isOpen: false,
      isLeaf: false,
      children: <Tree value={childNode} label="Child" _isRoot={false} />,
    });
    expect(screen.queryByRole('group')).not.toBeInTheDocument();
  });
});

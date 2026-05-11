import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {useDragTreeNode} from './useDragTreeNode';
import {OrderableTreeContext} from '../components/providers/OrderableTreeProvider';
import {TreeNode} from '../models';

const leafNode: TreeNode<object> = {
  identifier: 42,
  label: 'My Category',
  code: 'my_category',
  parentId: 1,
  childrenIds: [],
  data: {},
  type: 'leaf',
  childrenStatus: 'idle',
};

const rootNode: TreeNode<object> = {
  ...leafNode,
  identifier: 1,
  parentId: null,
  type: 'root',
};

describe('useDragTreeNode', () => {
  const mockSetDraggedNode = jest.fn();

  const createWrapper =
    (overrides: Partial<React.ContextType<typeof OrderableTreeContext>> = {}) =>
    ({children}: {children: React.ReactNode}) =>
      React.createElement(OrderableTreeContext.Provider, {
        value: {
          draggedNode: null,
          setDraggedNode: mockSetDraggedNode,
          endMove: jest.fn(),
          isActive: true,
          ...overrides,
        },
      }, children);

  it('isDraggable is false when context isActive is false', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 0), {
      wrapper: createWrapper({isActive: false}),
    });
    expect(result.current.isDraggable).toBe(false);
  });

  it('isDraggable is false for a root node even when active', () => {
    const {result} = renderHook(() => useDragTreeNode(rootNode, 0), {
      wrapper: createWrapper(),
    });
    expect(result.current.isDraggable).toBe(false);
  });

  it('isDraggable is true for a non-root node when active', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 0), {
      wrapper: createWrapper(),
    });
    expect(result.current.isDraggable).toBe(true);
  });

  it('isDragged returns false when no node is being dragged', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 0), {
      wrapper: createWrapper({draggedNode: null}),
    });
    expect(result.current.isDragged()).toBe(false);
  });

  it('isDragged returns true when draggedNode matches node identifier', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 0), {
      wrapper: createWrapper({draggedNode: {identifier: 42, parentId: 1, position: 0}}),
    });
    expect(result.current.isDragged()).toBe(true);
  });

  it('isDragged returns false when draggedNode identifier differs', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 0), {
      wrapper: createWrapper({draggedNode: {identifier: 99, parentId: 1, position: 0}}),
    });
    expect(result.current.isDragged()).toBe(false);
  });

  it('onDragStart calls setDraggedNode with identifier, parentId and index', () => {
    const {result} = renderHook(() => useDragTreeNode(leafNode, 2), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragStart();
    });

    expect(mockSetDraggedNode).toHaveBeenCalledWith({identifier: 42, parentId: 1, position: 2});
  });

  it('onDragStart throws when node has no parentId (root node)', () => {
    const {result} = renderHook(() => useDragTreeNode(rootNode, 0), {
      wrapper: createWrapper(),
    });
    expect(() => result.current.onDragStart()).toThrow();
  });
});

import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {useDropTreeNode} from './useDropTreeNode';
import {OrderableTreeContext} from '../components/providers/OrderableTreeProvider';
import {TreeNode} from '../models';

const treeNode: TreeNode<object> = {
  identifier: 10,
  label: 'Shoes',
  code: 'shoes',
  parentId: 1,
  childrenIds: [],
  data: {},
  type: 'leaf',
  childrenStatus: 'idle',
};

const draggedNode = {identifier: 99, parentId: 2, position: 0};

describe('useDropTreeNode', () => {
  const mockSetDraggedNode = jest.fn();
  const mockReorder = jest.fn();

  const createWrapper =
    (overrides: Partial<React.ContextType<typeof OrderableTreeContext>> = {}) =>
    ({children}: {children: React.ReactNode}) =>
      React.createElement(OrderableTreeContext.Provider, {
        value: {
          draggedNode,
          setDraggedNode: mockSetDraggedNode,
          endMove: jest.fn(),
          isActive: true,
          ...overrides,
        },
      }, children);

  // top=0, bottom=90 → topTierHeight=30, bottomTierHeight=60
  const mockElement = {
    getBoundingClientRect: () => ({top: 0, bottom: 90}),
  } as Element;

  it('onDragOver sets position "before" when cursor is in the top third', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 10});
    });

    expect(result.current.dropTarget).toEqual({parentId: 1, identifier: 10, position: 'before'});
  });

  it('onDragOver sets position "in" when cursor is in the middle third', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 45});
    });

    expect(result.current.dropTarget).toEqual({parentId: 1, identifier: 10, position: 'in'});
  });

  it('onDragOver sets position "after" when cursor is in the bottom third', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 75});
    });

    expect(result.current.dropTarget).toEqual({parentId: 1, identifier: 10, position: 'after'});
  });

  it('onDragOver does nothing when dragging over the same node', () => {
    const sameNode = {...treeNode, identifier: 99};
    const {result} = renderHook(() => useDropTreeNode(sameNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 45});
    });

    expect(result.current.dropTarget).toBeNull();
  });

  it('onDragOver does nothing for root node', () => {
    const rootNode = {...treeNode, type: 'root' as const, parentId: null};
    const {result} = renderHook(() => useDropTreeNode(rootNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 45});
    });

    expect(result.current.dropTarget).toBeNull();
  });

  it('placeholderPosition is "top" when drop target is "before" and hovering', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragEnter();
      result.current.onDragOver(mockElement, {x: 0, y: 10}); // 'before'
    });

    expect(result.current.placeholderPosition).toBe('top');
  });

  it('placeholderPosition is "middle" when drop target is "in" and hovering', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragEnter();
      result.current.onDragOver(mockElement, {x: 0, y: 45}); // 'in'
    });

    expect(result.current.placeholderPosition).toBe('middle');
  });

  it('placeholderPosition is "bottom" when drop target is "after" and hovering', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragEnter();
      result.current.onDragOver(mockElement, {x: 0, y: 75}); // 'after'
    });

    expect(result.current.placeholderPosition).toBe('bottom');
  });

  it('onDrop calls reorder with draggedNode identifier and current dropTarget', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragOver(mockElement, {x: 0, y: 10});
    });

    act(() => {
      result.current.onDrop();
    });

    expect(mockReorder).toHaveBeenCalledWith(
      99, // draggedNode.identifier
      {parentId: 1, identifier: 10, position: 'before'},
      expect.any(Function)
    );
  });

  it('onDrop does nothing when there is no dragged node', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper({draggedNode: null}),
    });

    act(() => {
      result.current.onDrop();
    });

    expect(mockReorder).not.toHaveBeenCalled();
  });

  it('onDragEnd clears draggedNode via context setDraggedNode', () => {
    const {result} = renderHook(() => useDropTreeNode(treeNode, mockReorder), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onDragEnd();
    });

    expect(mockSetDraggedNode).toHaveBeenCalledWith(null);
  });
});

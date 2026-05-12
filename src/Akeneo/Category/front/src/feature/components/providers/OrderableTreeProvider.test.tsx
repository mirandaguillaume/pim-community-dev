import React, {useContext} from 'react';
import {act, renderHook} from '@testing-library/react';
import {OrderableTreeProvider, OrderableTreeContext} from './OrderableTreeProvider';

const createWrapper = (isActive: boolean) =>
  ({children}: {children: React.ReactNode}) =>
    React.createElement(OrderableTreeProvider, {isActive}, children);

describe('OrderableTreeProvider', () => {
  it('exposes the isActive prop through context', () => {
    const {result} = renderHook(() => useContext(OrderableTreeContext), {wrapper: createWrapper(true)});
    expect(result.current.isActive).toBe(true);
  });

  it('initialises draggedNode to null', () => {
    const {result} = renderHook(() => useContext(OrderableTreeContext), {wrapper: createWrapper(false)});
    expect(result.current.draggedNode).toBeNull();
  });

  it('updates draggedNode when setDraggedNode is called', () => {
    const {result} = renderHook(() => useContext(OrderableTreeContext), {wrapper: createWrapper(true)});
    const node = {parentId: 0, position: 1, identifier: 42};
    act(() => result.current.setDraggedNode(node));
    expect(result.current.draggedNode).toEqual(node);
  });

  it('resets draggedNode to null when endMove is called', () => {
    const {result} = renderHook(() => useContext(OrderableTreeContext), {wrapper: createWrapper(true)});
    act(() => result.current.setDraggedNode({parentId: 0, position: 1, identifier: 42}));
    act(() => result.current.endMove());
    expect(result.current.draggedNode).toBeNull();
  });

  it('default context values are safe to call without a provider', () => {
    const {result} = renderHook(() => useContext(OrderableTreeContext));
    expect(result.current.isActive).toBe(false);
    expect(() => result.current.setDraggedNode(null)).not.toThrow();
    expect(() => result.current.endMove()).not.toThrow();
  });
});

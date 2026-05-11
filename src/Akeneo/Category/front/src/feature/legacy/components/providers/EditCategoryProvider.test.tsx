import React, {useContext} from 'react';
import {renderHook} from '@testing-library/react';
import {EditCategoryProvider, EditCategoryContext} from './EditCategoryProvider';

describe('EditCategoryProvider', () => {
  it('passes the setCanLeavePage prop through context', () => {
    const mockSetCanLeavePage = jest.fn();

    const wrapper = ({children}: {children: React.ReactNode}) =>
      React.createElement(EditCategoryProvider, {setCanLeavePage: mockSetCanLeavePage}, children);

    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper});

    result.current.setCanLeavePage(true);

    expect(mockSetCanLeavePage).toHaveBeenCalledWith(true);
  });

  it('default context value provides a no-op setCanLeavePage', () => {
    const {result} = renderHook(() => useContext(EditCategoryContext));
    expect(() => result.current.setCanLeavePage(false)).not.toThrow();
  });
});

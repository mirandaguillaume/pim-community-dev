import React, {useContext} from 'react';
import {act, renderHook} from '@testing-library/react';
import {CanLeavePageProvider, CanLeavePageContext} from './CanLeavePageProvider';

describe('CanLeavePageProvider', () => {
  it('passes setCanLeavePage through context', () => {
    const mockSetCanLeavePage = jest.fn();
    const mockSetLeavePageMessage = jest.fn();

    const wrapper = ({children}: {children: React.ReactNode}) =>
      React.createElement(CanLeavePageProvider, {
        setCanLeavePage: mockSetCanLeavePage,
        setLeavePageMessage: mockSetLeavePageMessage,
      }, children);

    const {result} = renderHook(() => useContext(CanLeavePageContext), {wrapper});

    act(() => result.current.setCanLeavePage(true));
    expect(mockSetCanLeavePage).toHaveBeenCalledWith(true);
  });

  it('passes setLeavePageMessage through context', () => {
    const mockSetCanLeavePage = jest.fn();
    const mockSetLeavePageMessage = jest.fn();

    const wrapper = ({children}: {children: React.ReactNode}) =>
      React.createElement(CanLeavePageProvider, {
        setCanLeavePage: mockSetCanLeavePage,
        setLeavePageMessage: mockSetLeavePageMessage,
      }, children);

    const {result} = renderHook(() => useContext(CanLeavePageContext), {wrapper});

    act(() => result.current.setLeavePageMessage('Are you sure?'));
    expect(mockSetLeavePageMessage).toHaveBeenCalledWith('Are you sure?');
  });

  it('default context provides no-op functions that do not throw', () => {
    const {result} = renderHook(() => useContext(CanLeavePageContext));
    expect(() => result.current.setCanLeavePage(false)).not.toThrow();
    expect(() => result.current.setLeavePageMessage('msg')).not.toThrow();
  });
});

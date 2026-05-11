import React from 'react';
import {renderHook} from '@testing-library/react';
import {useSaveStatus} from './useSaveStatus';
import {SaveStatusContext, Status} from '../components/providers/SaveStatusProvider';

describe('useSaveStatus', () => {
  it('throws when used outside a SaveStatusProvider', () => {
    expect(() => renderHook(() => useSaveStatus())).toThrow(
      'useSaveStatus must be used within a SaveStatusProvider'
    );
  });

  it('returns context value when wrapped in a SaveStatusProvider', () => {
    const mockHandleStatusListChange = jest.fn();
    const contextValue = {globalStatus: Status.EDITING, handleStatusListChange: mockHandleStatusListChange};

    const wrapper = ({children}: {children: React.ReactNode}) =>
      React.createElement(SaveStatusContext.Provider, {value: contextValue}, children);

    const {result} = renderHook(() => useSaveStatus(), {wrapper});

    expect(result.current.globalStatus).toBe(Status.EDITING);
    expect(result.current.handleStatusListChange).toBe(mockHandleStatusListChange);
  });
});

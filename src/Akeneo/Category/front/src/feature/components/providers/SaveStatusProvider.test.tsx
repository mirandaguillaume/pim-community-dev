import React from 'react';
import {renderHook, act} from '@testing-library/react';
import {SaveStatusProvider, Status} from './SaveStatusProvider';
import {useSaveStatus} from '../../hooks/useSaveStatus';

const createWrapper =
  () =>
  ({children}: {children: React.ReactNode}) =>
    React.createElement(SaveStatusProvider, null, children);

describe('SaveStatusProvider', () => {
  it('provides Status.SAVED as the initial globalStatus', () => {
    const {result} = renderHook(() => useSaveStatus(), {wrapper: createWrapper()});
    expect(result.current.globalStatus).toBe(Status.SAVED);
  });

  it('updates globalStatus when a field registers a new status', () => {
    const {result} = renderHook(() => useSaveStatus(), {wrapper: createWrapper()});

    act(() => {
      result.current.handleStatusListChange('field1', Status.EDITING);
    });

    expect(result.current.globalStatus).toBe(Status.EDITING);
  });

  it('resolves to the highest-priority status when multiple fields are registered', () => {
    const {result} = renderHook(() => useSaveStatus(), {wrapper: createWrapper()});

    act(() => {
      result.current.handleStatusListChange('field1', Status.ERRORS);
      result.current.handleStatusListChange('field2', Status.EDITING);
    });

    expect(result.current.globalStatus).toBe(Status.EDITING);
  });

  it('priority order is SAVED < ERRORS < SAVING < EDITING', () => {
    expect(Status.SAVED).toBeLessThan(Status.ERRORS);
    expect(Status.ERRORS).toBeLessThan(Status.SAVING);
    expect(Status.SAVING).toBeLessThan(Status.EDITING);
  });

  it('downgrades globalStatus when the high-priority field is updated to SAVED', () => {
    const {result} = renderHook(() => useSaveStatus(), {wrapper: createWrapper()});

    act(() => {
      result.current.handleStatusListChange('field1', Status.EDITING);
    });

    act(() => {
      result.current.handleStatusListChange('field1', Status.SAVED);
    });

    expect(result.current.globalStatus).toBe(Status.SAVED);
  });

  it('keeps the maximum when a lower-priority field is updated', () => {
    const {result} = renderHook(() => useSaveStatus(), {wrapper: createWrapper()});

    act(() => {
      result.current.handleStatusListChange('field1', Status.EDITING);
      result.current.handleStatusListChange('field2', Status.ERRORS);
    });

    act(() => {
      result.current.handleStatusListChange('field2', Status.SAVED);
    });

    expect(result.current.globalStatus).toBe(Status.EDITING);
  });
});

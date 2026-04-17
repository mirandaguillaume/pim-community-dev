import {renderHook} from '@testing-library/react';
import {fireEvent} from '@testing-library/react';
import {useValidateFormWithEnter} from '../useValidateFormWithEnter';

describe('useValidateFormWithEnter', () => {
  it('should call confirmCallback on Enter key', () => {
    const confirmCallback = jest.fn();
    renderHook(() => useValidateFormWithEnter(confirmCallback));
    fireEvent.keyDown(document, {code: 'Enter'});
    expect(confirmCallback).toHaveBeenCalledTimes(1);
  });

  it('should call confirmCallback on NumpadEnter key', () => {
    const confirmCallback = jest.fn();
    renderHook(() => useValidateFormWithEnter(confirmCallback));
    fireEvent.keyDown(document, {code: 'NumpadEnter'});
    expect(confirmCallback).toHaveBeenCalledTimes(1);
  });

  it('should not call callback on other keys', () => {
    const confirmCallback = jest.fn();
    renderHook(() => useValidateFormWithEnter(confirmCallback));
    fireEvent.keyDown(document, {code: 'Space'});
    expect(confirmCallback).not.toHaveBeenCalled();
  });

  it('should not call callback on Escape key', () => {
    const confirmCallback = jest.fn();
    renderHook(() => useValidateFormWithEnter(confirmCallback));
    fireEvent.keyDown(document, {code: 'Escape'});
    expect(confirmCallback).not.toHaveBeenCalled();
  });

  it('should remove listener on unmount', () => {
    const confirmCallback = jest.fn();
    const {unmount} = renderHook(() => useValidateFormWithEnter(confirmCallback));
    unmount();
    fireEvent.keyDown(document, {code: 'Enter'});
    expect(confirmCallback).not.toHaveBeenCalled();
  });
});

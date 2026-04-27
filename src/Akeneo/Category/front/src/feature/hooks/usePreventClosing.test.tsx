import {renderHook} from '@testing-library/react';
import {usePreventClosing} from './usePreventClosing';

describe('usePreventClosing', () => {
  test('it adds a beforeunload listener on mount and removes it on unmount', () => {
    const addSpy = jest.spyOn(window, 'addEventListener');
    const removeSpy = jest.spyOn(window, 'removeEventListener');

    const {unmount} = renderHook(() => usePreventClosing(() => false, 'Unsaved changes'));

    expect(addSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));

    unmount();
    expect(removeSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  test('it does not prevent unload when isDirty returns false', () => {
    renderHook(() => usePreventClosing(() => false, 'Unsaved changes'));

    const event = new Event('beforeunload') as BeforeUnloadEvent;
    const preventSpy = jest.spyOn(event, 'preventDefault');
    window.dispatchEvent(event);

    expect(preventSpy).not.toHaveBeenCalled();
  });

  test('it prevents unload and sets returnValue when isDirty returns true', () => {
    renderHook(() => usePreventClosing(() => true, 'You have unsaved changes'));

    const event = Object.assign(new Event('beforeunload'), {returnValue: ''}) as BeforeUnloadEvent;
    const preventSpy = jest.spyOn(event, 'preventDefault');
    window.dispatchEvent(event);

    expect(preventSpy).toHaveBeenCalled();
    expect(event.returnValue).toBe('You have unsaved changes');
  });
});

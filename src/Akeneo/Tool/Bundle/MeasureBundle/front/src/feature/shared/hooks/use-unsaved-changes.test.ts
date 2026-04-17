import {useUnsavedChanges} from './use-unsaved-changes';
import {renderHook, act} from '@testing-library/react';

test('It starts as not modified', () => {
  const {result} = renderHook(() => useUnsavedChanges('initial', 'unsaved'));

  const [isModified] = result.current;
  expect(isModified).toBe(false);
});

test('It detects modification when entity changes', () => {
  let entity = 'nice';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'nice_message'));

  expect(result.current[0]).toBe(false);

  entity = 'niceee';
  rerender();

  expect(result.current[0]).toBe(true);
});

test('It stays unmodified when null entity is provided', () => {
  const {result} = renderHook(() => useUnsavedChanges(null, 'nice_message'));

  expect(result.current[0]).toBe(false);
});

test('It ignores null value updates without setting initialValue', () => {
  let entity: string | null = null;
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  // Still null, should remain unmodified
  rerender();
  expect(result.current[0]).toBe(false);

  // Now provide a real value - this becomes the initial value
  entity = 'real';
  rerender();
  expect(result.current[0]).toBe(false);

  // Change it - should now be modified
  entity = 'changed';
  rerender();
  expect(result.current[0]).toBe(true);
});

test('It resets to unmodified after calling resetValue', () => {
  let entity = 'nice';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'nice_message'));

  expect(result.current[0]).toBe(false);

  // Modify the entity
  entity = 'niceee';
  rerender();
  expect(result.current[0]).toBe(true);

  // Reset ("save")
  const resetState = result.current[1];
  act(() => resetState());
  rerender();
  expect(result.current[0]).toBe(false);
});

test('After reset, going back to the pre-reset value shows as modified', () => {
  let entity = 'nice';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'nice_message'));

  // Modify
  entity = 'niceee';
  rerender();
  expect(result.current[0]).toBe(true);

  // Reset with 'niceee' as new baseline
  act(() => result.current[1]());
  rerender();
  expect(result.current[0]).toBe(false);

  // Go back to original 'nice' - this is now different from the reset baseline
  entity = 'nice';
  rerender();
  expect(result.current[0]).toBe(true);
});

test('It stays unmodified when entity value does not change', () => {
  let entity = 'same';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  // Re-render with same value
  entity = 'same';
  rerender();
  expect(result.current[0]).toBe(false);
});

test('It detects modification with complex objects', () => {
  let entity: {name: string; items: string[]} = {name: 'test', items: ['a', 'b']};
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  // Change an item
  entity = {name: 'test', items: ['a', 'c']};
  rerender();
  expect(result.current[0]).toBe(true);
});

test('It stays unmodified when complex object is structurally the same', () => {
  let entity: {name: string; count: number} = {name: 'test', count: 1};
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  // Same structure and values, different reference
  entity = {name: 'test', count: 1};
  rerender();
  expect(result.current[0]).toBe(false);
});

test('It detects when item is added to an array', () => {
  let entity = ['a', 'b'];
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  entity = ['a', 'b', 'c'];
  rerender();
  expect(result.current[0]).toBe(true);
});

test('It detects when item is removed from an array', () => {
  let entity = ['a', 'b', 'c'];
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  entity = ['a', 'b'];
  rerender();
  expect(result.current[0]).toBe(true);
});

test('It returns false after modifying and reverting back to the original', () => {
  let entity = 'original';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  entity = 'modified';
  rerender();
  expect(result.current[0]).toBe(true);

  // Revert back
  entity = 'original';
  rerender();
  expect(result.current[0]).toBe(false);
});

test('The resetValue callback sets the current entity as the new baseline', () => {
  let entity = 'v1';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  entity = 'v2';
  rerender();
  expect(result.current[0]).toBe(true);

  // Reset with v2
  act(() => result.current[1]());
  rerender();
  expect(result.current[0]).toBe(false);

  // v2 is now the baseline; v3 should be modified
  entity = 'v3';
  rerender();
  expect(result.current[0]).toBe(true);

  // Going back to v2 should be unmodified
  entity = 'v2';
  rerender();
  expect(result.current[0]).toBe(false);
});

test('It compares using JSON.stringify so object identity does not matter', () => {
  const entity1 = {key: 'value'};
  let entity = entity1;
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'msg'));

  expect(result.current[0]).toBe(false);

  // New object reference, same content
  entity = {key: 'value'};
  rerender();
  expect(result.current[0]).toBe(false);
});

test('resetValue sets isModified to false (not true)', () => {
  let entity = 'start';
  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'unsaved'));

  // Modify to trigger isModified = true
  entity = 'changed';
  rerender();
  expect(result.current[0]).toBe(true);

  // Call resetValue -- must set isModified to exactly false
  act(() => result.current[1]());

  // Immediately after reset, isModified must be false
  expect(result.current[0]).toBe(false);

  // Re-render with the same "changed" value that was reset as baseline
  rerender();
  expect(result.current[0]).toBe(false);
});

test('It registers a beforeunload event listener on window', () => {
  const addSpy = jest.spyOn(window, 'addEventListener');

  renderHook(() => useUnsavedChanges('val', 'unsaved changes'));

  expect(addSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));

  addSpy.mockRestore();
});

test('It removes the beforeunload listener on unmount', () => {
  const removeSpy = jest.spyOn(window, 'removeEventListener');

  const {unmount} = renderHook(() => useUnsavedChanges('val', 'unsaved'));

  unmount();

  expect(removeSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));

  removeSpy.mockRestore();
});

test('The beforeunload handler calls preventDefault and sets returnValue when modified', () => {
  let entity = 'start';
  const addSpy = jest.spyOn(window, 'addEventListener');

  const {result, rerender} = renderHook(() => useUnsavedChanges(entity, 'You have unsaved changes'));

  // Get the actual handler registered for beforeunload
  const beforeUnloadCall = addSpy.mock.calls.find(([eventName]) => eventName === 'beforeunload');
  expect(beforeUnloadCall).toBeDefined();
  const handler = beforeUnloadCall![1] as (event: BeforeUnloadEvent) => string | undefined;

  // Modify the entity
  entity = 'modified';
  rerender();
  expect(result.current[0]).toBe(true);

  // Get the updated handler (it changes when isModified changes)
  const updatedCall = addSpy.mock.calls.filter(([eventName]) => eventName === 'beforeunload').pop();
  const updatedHandler = updatedCall![1] as (event: BeforeUnloadEvent) => string | undefined;

  // Simulate calling the handler directly
  const event = {preventDefault: jest.fn(), returnValue: ''} as unknown as BeforeUnloadEvent;
  const returnVal = updatedHandler(event);

  expect(event.preventDefault).toHaveBeenCalled();
  expect(event.returnValue).toBe('You have unsaved changes');
  expect(returnVal).toBe('You have unsaved changes');

  addSpy.mockRestore();
});

test('The beforeunload handler does nothing when not modified', () => {
  const addSpy = jest.spyOn(window, 'addEventListener');

  renderHook(() => useUnsavedChanges('start', 'You have unsaved changes'));

  const beforeUnloadCall = addSpy.mock.calls.find(([eventName]) => eventName === 'beforeunload');
  expect(beforeUnloadCall).toBeDefined();
  const handler = beforeUnloadCall![1] as (event: BeforeUnloadEvent) => string | undefined;

  const event = {preventDefault: jest.fn(), returnValue: ''} as unknown as BeforeUnloadEvent;
  handler(event);

  expect(event.preventDefault).not.toHaveBeenCalled();
  expect(event.returnValue).toBe('');

  addSpy.mockRestore();
});

test('The beforeunload handler uses the provided message', () => {
  const message = 'Custom unsaved warning';
  let entity = 'a';
  const addSpy = jest.spyOn(window, 'addEventListener');

  const {rerender} = renderHook(() => useUnsavedChanges(entity, message));

  entity = 'b';
  rerender();

  const updatedCall = addSpy.mock.calls.filter(([eventName]) => eventName === 'beforeunload').pop();
  const handler = updatedCall![1] as (event: BeforeUnloadEvent) => string | undefined;

  const event = {preventDefault: jest.fn(), returnValue: ''} as unknown as BeforeUnloadEvent;
  const returnVal = handler(event);

  expect(event.returnValue).toBe(message);
  expect(returnVal).toBe(message);

  addSpy.mockRestore();
});

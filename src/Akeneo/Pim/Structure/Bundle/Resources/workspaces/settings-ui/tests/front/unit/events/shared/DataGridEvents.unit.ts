import {
  handleDrop,
  handleDragEnd,
  handleDragStart,
  handleDragOver,
} from '@akeneo-pim-community/settings-ui/src/events/shared/DataGridEvents';

const makeEvent = (overrides: Partial<any> = {}): any => ({
  stopPropagation: jest.fn(),
  preventDefault: jest.fn(),
  persist: jest.fn(),
  dataTransfer: {setDragImage: jest.fn()},
  clientY: 0,
  target: {getBoundingClientRect: () => ({top: 0, bottom: 100})},
  ...overrides,
});

test('handleDrop calls stopPropagation, preventDefault, persist, and dropCallback', () => {
  const dropCallback = jest.fn();
  const event = makeEvent();
  handleDrop(event, dropCallback);
  expect(event.stopPropagation).toHaveBeenCalled();
  expect(event.preventDefault).toHaveBeenCalled();
  expect(dropCallback).toHaveBeenCalled();
});

test('handleDragEnd calls stopPropagation, preventDefault, and dragEndCallback', () => {
  const dragEndCallback = jest.fn();
  const event = makeEvent();
  handleDragEnd(event, dragEndCallback);
  expect(event.stopPropagation).toHaveBeenCalled();
  expect(dragEndCallback).toHaveBeenCalled();
});

test('handleDragStart sets drag image when dragImage is provided', () => {
  const dragImage = {} as Element;
  const event = makeEvent();
  handleDragStart(event, dragImage);
  expect(event.stopPropagation).toHaveBeenCalled();
  expect(event.dataTransfer.setDragImage).toHaveBeenCalledWith(dragImage, 0, 0);
});

test('handleDragStart does not call setDragImage when dragImage is null', () => {
  const event = makeEvent();
  handleDragStart(event, null);
  expect(event.dataTransfer.setDragImage).not.toHaveBeenCalled();
});

test('handleDragOver calls dragDownCallback when dragging down past midpoint', () => {
  const dragDownCallback = jest.fn();
  const dragUpCallback = jest.fn();
  const sameData = jest.fn().mockReturnValue(false);

  // draggedIndex=0 < activeDropZoneIndex=1, clientY=60 >= hoverMiddleY=50 → dragDown
  const event = makeEvent({clientY: 60, target: {getBoundingClientRect: () => ({top: 10, bottom: 110})}});
  handleDragOver(event, 0, 'a', 1, 'b', dragDownCallback, dragUpCallback, sameData);

  expect(dragDownCallback).toHaveBeenCalledWith('a', 'b');
  expect(dragUpCallback).not.toHaveBeenCalled();
});

test('handleDragOver calls dragUpCallback when dragging up past midpoint', () => {
  const dragDownCallback = jest.fn();
  const dragUpCallback = jest.fn();
  const sameData = jest.fn().mockReturnValue(false);

  // draggedIndex=1 > activeDropZoneIndex=0, clientY=20 <= hoverMiddleY=50 → dragUp
  const event = makeEvent({clientY: 20, target: {getBoundingClientRect: () => ({top: 10, bottom: 110})}});
  handleDragOver(event, 1, 'b', 0, 'a', dragDownCallback, dragUpCallback, sameData);

  expect(dragUpCallback).toHaveBeenCalledWith('b', 'a');
  expect(dragDownCallback).not.toHaveBeenCalled();
});

test('handleDragOver does nothing when sameData returns true', () => {
  const dragDownCallback = jest.fn();
  const dragUpCallback = jest.fn();
  const sameData = jest.fn().mockReturnValue(true);

  const event = makeEvent({clientY: 60});
  handleDragOver(event, 0, 'a', 1, 'a', dragDownCallback, dragUpCallback, sameData);

  expect(dragDownCallback).not.toHaveBeenCalled();
  expect(dragUpCallback).not.toHaveBeenCalled();
});

test('handleDragOver does nothing when draggedData is null', () => {
  const dragDownCallback = jest.fn();
  const event = makeEvent();
  handleDragOver(event, 0, null, 1, 'b', dragDownCallback, jest.fn(), jest.fn());
  expect(dragDownCallback).not.toHaveBeenCalled();
});

import React from 'react';
import {render, fireEvent} from '@testing-library/react';
import CreateViewFields from '../../../../Resources/public/js/grid/CreateViewFields';

const labels = {chooseLabel: 'Label', placeholder: 'Type a label', chooseType: 'Public view'};

const noop = () => {};

test('renders the label input and the private-by-default type toggle', () => {
  const {container} = render(<CreateViewFields labels={labels} onChange={noop} onSubmit={noop} />);

  expect(container.querySelector('input[name="new-view-label"]')).not.toBeNull();
  const toggle = container.querySelector('.AknCreateView-typeSelector')!;
  expect(toggle.classList.contains('AknSelectButton--selected')).toBe(true);
});

test('lifts the typed label to onChange (still private)', () => {
  const onChange = jest.fn();
  const {container} = render(<CreateViewFields labels={labels} onChange={onChange} onSubmit={noop} />);

  fireEvent.change(container.querySelector('input[name="new-view-label"]')!, {target: {value: 'My view'}});

  expect(onChange).toHaveBeenLastCalledWith({label: 'My view', isPrivate: true});
});

test('toggles isPrivate and the selected class on type-selector click', () => {
  const onChange = jest.fn();
  const {container} = render(<CreateViewFields labels={labels} onChange={onChange} onSubmit={noop} />);

  fireEvent.click(container.querySelector('.AknCreateView-typeSelector')!);

  expect(onChange).toHaveBeenLastCalledWith({label: '', isPrivate: false});
  expect(container.querySelector('.AknCreateView-typeSelector')!.classList.contains('AknSelectButton--selected')).toBe(
    false
  );
});

test('submits on Enter only once the label is non-empty', () => {
  const onSubmit = jest.fn();
  const {container} = render(<CreateViewFields labels={labels} onChange={noop} onSubmit={onSubmit} />);
  const input = container.querySelector('input[name="new-view-label"]')!;

  fireEvent.keyPress(input, {key: 'Enter', charCode: 13});
  expect(onSubmit).not.toHaveBeenCalled();

  fireEvent.change(input, {target: {value: 'My view'}});
  fireEvent.keyPress(input, {key: 'Enter', charCode: 13});
  expect(onSubmit).toHaveBeenCalledTimes(1);
});

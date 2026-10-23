import React from 'react';
import {TextAreaInput} from './TextAreaInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'Cool'}});
  expect(handleChange).toHaveBeenCalledWith('Cool');
});

test('it renders and does not call onChange if readOnly', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" readOnly={true} value="Nice" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'Cool'}});
  expect(handleChange).not.toHaveBeenCalledWith('Cool');
});

test('it renders and displays the character left label', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput
        id="myInput"
        characterLeftLabel="100 character remaining"
        readOnly={true}
        value="Nice"
        onChange={handleChange}
      />
    </>
  );

  expect(screen.getByText('100 character remaining')).toBeInTheDocument();
});

test('it renders the rich text editor with its value', () => {
  const {container} = render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice RTF content" isRichText={true} onChange={jest.fn()} />
    </>
  );

  expect(container.querySelector('[contenteditable="true"]')).not.toBeNull();
  expect(screen.getByText('Nice RTF content')).toBeInTheDocument();
});

test('it hides the toolbar and the editing surface when read only', () => {
  const {container} = render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice RTF content" isRichText={true} readOnly onChange={jest.fn()} />
    </>
  );

  expect(container.querySelector('[role="toolbar"]')).toBeNull();
  expect(container.querySelector('[contenteditable="true"]')).toBeNull();
  expect(screen.getByText('Nice RTF content')).toBeInTheDocument();
});

// This file DOES run in CI, through the `Launch tests on the DSM` job of dsm-test.yml. An earlier
// comment here claimed the opposite; the job that caught a stale test in this very file disproved
// it. The twin guard in the Category workspace is kept because it covers the consumer's wiring,
// not because this one is unexecuted.
test('the rich text toolbar offers exactly the expected controls', () => {
  const {container} = render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice RTF content" isRichText={true} onChange={jest.fn()} />
    </>
  );

  const toolbar = container.querySelector('[role="toolbar"]');
  expect(toolbar).not.toBeNull();

  const groups = Array.from(toolbar?.children ?? []).map(child => child.getAttribute('aria-label'));
  expect(groups).toEqual([
    'inline-control',
    'block-type-control',
    'font-size-control',
    'font-family-control',
    'list-control',
    'link-control',
    'clear-formatting-control',
  ]);
});

test('TextAreaInput supports forwardRef', () => {
  const ref = {current: null};

  render(<TextAreaInput value="nice" onChange={jest.fn()} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TextAreaInput supports ...rest props', () => {
  render(<TextAreaInput value="nice" onChange={jest.fn()} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it strips a javascript: scheme out of a stored link', () => {
  const {container} = render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput
        id="myInput"
        value={'<p><a href="javascript:alert(1)">click me</a></p>'}
        isRichText={true}
        onChange={jest.fn()}
      />
    </>
  );

  expect(screen.getByText('click me')).toBeInTheDocument();
  expect(container.innerHTML).not.toContain('javascript:');
});

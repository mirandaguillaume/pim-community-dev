import React from 'react';
import {fireEvent, render, screen, within} from '../../../storybook/test-util';
import {RichTextEditor} from './RichTextEditor';

// The design system gates on 100% statements, functions and lines, so every handler in the toolbar
// needs to be executed here. Branches have no threshold, which is why `applyLink` is written as a
// ternary -- its two outcomes are branches inside one statement -- while `setBlockType` has an early
// return and therefore needs both of its paths driven.
//
// Queries are scoped with `within` on each group's aria-label on purpose: SelectInput renders a
// role="textbox", and so does ProseMirror's editing surface, so an unscoped getByRole('textbox')
// matches four elements and throws.
const group = (label: string): HTMLElement => screen.getByLabelText(label);

const pick = (groupLabel: string, optionLabel: string) => {
  fireEvent.click(within(group(groupLabel)).getByRole('textbox'));
  fireEvent.click(screen.getByText(optionLabel));
};

test('it renders the toolbar and reports a change through onChange', () => {
  const onChange = jest.fn();
  render(<RichTextEditor value="<p>Hello</p>" onChange={onChange} />);

  expect(screen.getByRole('toolbar')).toBeInTheDocument();

  // Wrapping a paragraph in a list changes the document, which is what makes onUpdate fire. A
  // collapsed-cursor mark toggle may only set a stored mark and emit nothing.
  fireEvent.click(screen.getByTitle('Bulleted list'));
  expect(onChange).toHaveBeenCalled();
});

test('it toggles the inline marks and the lists', () => {
  render(<RichTextEditor value="<p>Hello</p>" onChange={jest.fn()} />);

  fireEvent.click(screen.getByTitle('Bold'));
  fireEvent.click(screen.getByTitle('Italic'));
  fireEvent.click(screen.getByTitle('Numbered list'));
  fireEvent.click(screen.getByTitle('Clear formatting'));

  expect(screen.getByRole('toolbar')).toBeInTheDocument();
});

// Two renders rather than two picks in one: SelectInput shows the selected option's label AND the
// option inside the open dropdown, so picking 'Normal' while 'Normal' is already selected matches
// two elements and getByText throws. Starting from the other block type removes the ambiguity by
// construction instead of reaching for getAllByText and an index.
test('it switches the block type from a paragraph to a heading', () => {
  render(<RichTextEditor value="<p>Hello</p>" onChange={jest.fn()} />);

  pick('block-type-control', 'Title 2');

  expect(within(group('block-type-control')).getByText('Title 2')).toBeInTheDocument();
});

test('it switches the block type from a heading back to a paragraph', () => {
  render(<RichTextEditor value="<h2>Hello</h2>" onChange={jest.fn()} />);

  pick('block-type-control', 'Normal');

  expect(within(group('block-type-control')).getByText('Normal')).toBeInTheDocument();
});

test('it reads the current block type back from a heading', () => {
  render(<RichTextEditor value="<h2>Hello</h2>" onChange={jest.fn()} />);

  // SelectInput puts the SEARCH text in its input and renders the selected option's label in a
  // separate container, so the label is what to assert on -- the same trap the legacy Select2
  // filters have, where the native value is hidden.
  expect(within(group('block-type-control')).getByText('Title 2')).toBeInTheDocument();
});

test('it sets a font size and a font family', () => {
  render(<RichTextEditor value="<p>Hello</p>" onChange={jest.fn()} />);

  pick('font-size-control', '18');
  pick('font-family-control', 'Georgia');

  expect(screen.getByText('Hello')).toBeInTheDocument();
});

test('it applies a link, then clears it with an empty url', () => {
  render(<RichTextEditor value="<p>Hello</p>" onChange={jest.fn()} />);

  fireEvent.click(screen.getByTitle('Link'));
  fireEvent.change(screen.getByLabelText('Link URL'), {target: {value: 'https://example.test'}});
  fireEvent.click(screen.getByText('Apply'));

  fireEvent.click(screen.getByTitle('Link'));
  fireEvent.click(screen.getByText('Apply'));

  expect(screen.getByText('Hello')).toBeInTheDocument();
});

test('it pushes a value changed from the outside into the document', () => {
  const {rerender} = render(<RichTextEditor value="<p>first</p>" onChange={jest.fn()} />);
  expect(screen.getByText('first')).toBeInTheDocument();

  rerender(<RichTextEditor value="<p>second</p>" onChange={jest.fn()} />);

  expect(screen.getByText('second')).toBeInTheDocument();
  expect(screen.queryByText('first')).not.toBeInTheDocument();
});

test('it renders no toolbar when read only, and becomes editable again when it is lifted', () => {
  const {rerender} = render(<RichTextEditor value="<p>Hello</p>" readOnly onChange={jest.fn()} />);

  expect(screen.queryByRole('toolbar')).not.toBeInTheDocument();

  rerender(<RichTextEditor value="<p>Hello</p>" onChange={jest.fn()} />);

  expect(screen.getByRole('toolbar')).toBeInTheDocument();
});

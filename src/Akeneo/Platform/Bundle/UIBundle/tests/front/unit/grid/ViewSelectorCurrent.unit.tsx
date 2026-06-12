import React from 'react';
import {render} from '@testing-library/react';
import ViewSelectorCurrent from '../../../../Resources/public/js/grid/ViewSelectorCurrent';

const view = (over = {}) => ({text: 'My view', ...over});

test('renders the current view label inside the select2 selection markup', () => {
  const {container} = render(<ViewSelectorCurrent view={view()} dirtyFilters={false} dirtyColumns={false} />);

  const current = container.querySelector('.select2-selection-label-view .current')!;
  expect(current.textContent!.trim()).toBe('My view');
});

test('prefixes a dirty marker when filters are modified', () => {
  const {container} = render(<ViewSelectorCurrent view={view()} dirtyFilters={true} dirtyColumns={false} />);

  expect(container.querySelector('.current')!.textContent!.trim().startsWith('*')).toBe(true);
});

test('prefixes a dirty marker when columns are modified', () => {
  const {container} = render(<ViewSelectorCurrent view={view()} dirtyFilters={false} dirtyColumns={true} />);

  expect(container.querySelector('.current')!.textContent!.trim().startsWith('*')).toBe(true);
});

test('does not prefix a marker when nothing is dirty', () => {
  const {container} = render(<ViewSelectorCurrent view={view()} dirtyFilters={false} dirtyColumns={false} />);

  expect(container.querySelector('.current')!.textContent).not.toContain('*');
});

test('keeps the before/after extension drop-zone spans', () => {
  const {container} = render(<ViewSelectorCurrent view={view()} dirtyFilters={false} dirtyColumns={false} />);

  expect(container.querySelector('.before[data-drop-zone="before"]')).not.toBeNull();
  expect(container.querySelector('.after[data-drop-zone="after"]')).not.toBeNull();
});

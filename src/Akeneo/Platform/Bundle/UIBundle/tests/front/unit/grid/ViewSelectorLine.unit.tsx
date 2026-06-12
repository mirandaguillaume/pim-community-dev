import React from 'react';
import {render} from '@testing-library/react';
import ViewSelectorLine from '../../../../Resources/public/js/grid/ViewSelectorLine';

const view = (over = {}) => ({id: 1, text: 'My view', type: 'private', ...over});

test('renders the view label inside the select2 result markup', () => {
  const {container} = render(<ViewSelectorLine view={view()} isCurrent={false} publicLabel="Public" />);

  const label = container.querySelector('.select2-result-label-view .view-line .view-label')!;
  expect(label.textContent).toBe('My view');
});

test('marks the label as current when isCurrent is true, not otherwise', () => {
  const on = render(<ViewSelectorLine view={view()} isCurrent={true} publicLabel="Public" />);
  expect(on.container.querySelector('.view-label')!.classList.contains('view-label-current')).toBe(true);

  const off = render(<ViewSelectorLine view={view()} isCurrent={false} publicLabel="Public" />);
  expect(off.container.querySelector('.view-label')!.classList.contains('view-label-current')).toBe(false);
});

test('renders the public-type badge with the translated label only for public views', () => {
  const pub = render(<ViewSelectorLine view={view({type: 'public'})} isCurrent={false} publicLabel="Public view" />);
  const badge = pub.container.querySelector('.view-type')!;
  expect(badge).not.toBeNull();
  expect(badge.textContent).toBe('Public view');

  const priv = render(<ViewSelectorLine view={view({type: 'private'})} isCurrent={false} publicLabel="Public view" />);
  expect(priv.container.querySelector('.view-type')).toBeNull();
});

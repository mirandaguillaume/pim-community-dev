import React from 'react';
import {render} from '@testing-library/react';
import {CompletenessBadge} from '../../../Resources/public/js/datagrid/cell/CompletenessBadge';

test('renders a success badge with the success modifier class', () => {
  const {container} = render(<CompletenessBadge level="success" label="100%" />);
  const badge = container.querySelector('span')!;

  expect(badge.className).toBe('AknBadge AknBadge--success');
  expect(badge.textContent).toBe('100%');
});

test('renders a warning badge with the warning modifier class', () => {
  const {container} = render(<CompletenessBadge level="warning" label="42%" />);
  const badge = container.querySelector('span')!;

  expect(badge.className).toBe('AknBadge AknBadge--warning');
  expect(badge.textContent).toBe('42%');
});

test('renders an important badge with the important modifier class', () => {
  const {container} = render(<CompletenessBadge level="important" label="3 / 5" />);
  const badge = container.querySelector('span')!;

  expect(badge.className).toBe('AknBadge AknBadge--important');
  expect(badge.textContent).toBe('3 / 5');
});

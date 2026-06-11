import React from 'react';
import {render} from '@testing-library/react';
import {EnabledBadge} from '../../../Resources/public/js/datagrid/cell/EnabledBadge';

test('renders an enabled badge with the enabled status classes and label', () => {
  const {container} = render(<EnabledBadge enabled={true} label="Enabled" />);
  const badge = container.querySelector('div')!;

  expect(badge.className).toBe('AknBadge AknBadge--enabled status-enabled');
  expect(badge.textContent).toBe('Enabled');
});

test('renders a disabled badge with the disabled status classes and label', () => {
  const {container} = render(<EnabledBadge enabled={false} label="Disabled" />);
  const badge = container.querySelector('div')!;

  expect(badge.className).toBe('AknBadge AknBadge--disabled status-disabled');
  expect(badge.textContent).toBe('Disabled');
});

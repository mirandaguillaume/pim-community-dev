import React from 'react';
import {render} from '@testing-library/react';
import ViewSelectorActionLink from '../../../../Resources/public/js/grid/ViewSelectorActionLink';

test('renders a create action link with the menu-link markup', () => {
  const {container} = render(<ViewSelectorActionLink action="create" label="Create" />);

  const link = container.querySelector('a')!;
  expect(link.classList.contains('AknDropdown-menuLink')).toBe(true);
  expect(link.classList.contains('create')).toBe(true);
  expect(link.textContent).toBe('Create');
});

test('renders a remove action link', () => {
  const {container} = render(<ViewSelectorActionLink action="remove" label="Remove" />);

  expect(container.querySelector('a')!.classList.contains('remove')).toBe(true);
});

test('applies the hidden modifier to the save link only when hidden is true', () => {
  const shown = render(<ViewSelectorActionLink action="save" label="Save" hidden={false} />);
  expect(shown.container.querySelector('a')!.classList.contains('AknDropdown-menuLink--hidden')).toBe(false);

  const hidden = render(<ViewSelectorActionLink action="save" label="Save" hidden={true} />);
  expect(hidden.container.querySelector('a')!.classList.contains('AknDropdown-menuLink--hidden')).toBe(true);
});

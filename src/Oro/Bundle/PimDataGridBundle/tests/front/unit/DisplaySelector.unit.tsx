import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {DisplaySelector} from '../../../Resources/public/js/datagrid/DisplaySelector';

const types = {
  list: {label: 'List'},
  gallery: {label: 'Gallery'},
};

test('It renders the selected type label and opens the menu on toggle click', () => {
  renderWithProviders(<DisplaySelector types={types} selectedType="list" displayLabel="Views" />);

  expect(screen.getByText('List')).toBeInTheDocument();
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('List'));
  expect(screen.getByText('Gallery')).toBeInTheDocument();
});

test('It closes the menu on a second toggle click', () => {
  renderWithProviders(<DisplaySelector types={types} selectedType="list" displayLabel="Views" />);

  // Open: only the span "List" exists (menu closed), so getByText is unambiguous.
  userEvent.click(screen.getByText('List'));
  expect(screen.getByText('Gallery')).toBeInTheDocument();

  // Close: menu is now open so both the toggle span and the list item have text "List".
  // getAllByText[0] is the span inside the toggle button.
  userEvent.click(screen.getAllByText('List')[0]);
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();
});

test('It marks the selected item active and keeps data-type hooks', () => {
  renderWithProviders(<DisplaySelector types={types} selectedType="gallery" displayLabel="Views" />);

  // Gallery is selectedType; its span is the only "Gallery" element before opening.
  userEvent.click(screen.getByText('Gallery'));
  const items = document.querySelectorAll('.display-selector-item');
  expect(items).toHaveLength(2);
  expect(items[1].getAttribute('data-type')).toBe('gallery');
  expect(items[1].querySelector('a')!.className).toContain('AknDropdown-menuLink--active');
});

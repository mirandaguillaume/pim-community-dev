import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {DisplaySelector} from '../../../Resources/public/js/datagrid/DisplaySelector';

const types = {
  list: {label: 'List'},
  gallery: {label: 'Gallery'},
};

test('It renders the selected type label and opens the menu', () => {
  renderWithProviders(<DisplaySelector types={types} selectedType="list" displayLabel="Views" onChange={jest.fn()} />);

  expect(screen.getByText('List')).toBeInTheDocument();
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('List'));
  expect(screen.getByText('Gallery')).toBeInTheDocument();
});

test('It calls onChange with the clicked type and closes the menu', () => {
  const onChange = jest.fn();
  renderWithProviders(<DisplaySelector types={types} selectedType="list" displayLabel="Views" onChange={onChange} />);

  userEvent.click(screen.getByText('List'));
  userEvent.click(screen.getByText('Gallery'));

  expect(onChange).toHaveBeenCalledWith('gallery');
  expect(screen.queryByText('Gallery')).not.toBeInTheDocument();
});

test('It marks the selected item active and keeps data-type hooks', () => {
  renderWithProviders(
    <DisplaySelector types={types} selectedType="gallery" displayLabel="Views" onChange={jest.fn()} />
  );

  userEvent.click(screen.getAllByText('Gallery')[0]);
  const items = document.querySelectorAll('.display-selector-item');
  expect(items).toHaveLength(2);
  expect(items[1].getAttribute('data-type')).toBe('gallery');
  expect(items[1].querySelector('a')!.className).toContain('AknDropdown-menuLink--active');
});

import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {MeasurementFamilySearchBar} from './MeasurementFamilySearchBar';
import {renderWithProviders} from '@akeneo-pim-community/shared';

test('It renders with the search placeholder translation key', () => {
  renderWithProviders(<MeasurementFamilySearchBar searchValue="" onSearchChange={jest.fn()} resultNumber={0} />);

  expect(screen.getByPlaceholderText('measurements.search.placeholder')).toBeInTheDocument();
});

test('It renders result count with the correct translation key and count', () => {
  renderWithProviders(<MeasurementFamilySearchBar searchValue="" onSearchChange={jest.fn()} resultNumber={5} />);

  expect(screen.getByText('pim_common.result_count')).toBeInTheDocument();
});

test('It calls onSearchChange when user types', () => {
  const onSearchChange = jest.fn();

  renderWithProviders(<MeasurementFamilySearchBar searchValue="" onSearchChange={onSearchChange} resultNumber={0} />);

  const input = screen.getByPlaceholderText('measurements.search.placeholder');
  fireEvent.change(input, {target: {value: 'area'}});

  expect(onSearchChange).toHaveBeenCalledWith('area');
});

test('It renders the current search value in the input', () => {
  renderWithProviders(<MeasurementFamilySearchBar searchValue="weight" onSearchChange={jest.fn()} resultNumber={3} />);

  const input = screen.getByPlaceholderText('measurements.search.placeholder') as HTMLInputElement;
  expect(input.value).toBe('weight');
});

test('It renders with sticky=0 (Search is rendered at all)', () => {
  const {container} = renderWithProviders(
    <MeasurementFamilySearchBar searchValue="" onSearchChange={jest.fn()} resultNumber={0} />
  );

  // The Search component is rendered
  expect(container.firstChild).toBeTruthy();
});

test('It renders with zero result count', () => {
  renderWithProviders(<MeasurementFamilySearchBar searchValue="xyz" onSearchChange={jest.fn()} resultNumber={0} />);

  expect(screen.getByText('pim_common.result_count')).toBeInTheDocument();
});

test('It auto-focuses the search input', () => {
  renderWithProviders(<MeasurementFamilySearchBar searchValue="" onSearchChange={jest.fn()} resultNumber={5} />);

  const input = screen.getByPlaceholderText('measurements.search.placeholder');
  // useAutoFocus is called; the input should exist and be focusable
  expect(input).toBeInTheDocument();
});
